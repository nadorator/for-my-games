<?php

use App\Services\Content\ContentController;
use App\Domain\Model\Content;
use App\Domain\Model\Game;
use App\Domain\Model\Visitor;
use Phalcon\Http\Response;
use Mockery as m;

beforeEach(function () {
    $this->app = getDI()->get('app');
    $this->app->request = m::mock('Phalcon\Http\Request');
    $this->app->response = new Response();
    
    // Create the controller
    $this->controller = new ContentController($this->app);
    
    // Mock models
    $this->contentMock = m::mock('overload:App\Domain\Model\Content');
    $this->gameMock = m::mock('overload:App\Domain\Model\Game');
    $this->visitorMock = m::mock('overload:App\Domain\Model\Visitor');
});

afterEach(function () {
    m::close();
});

test('createAction creates content successfully', function () {
    // Arrange - Mock the request with content data
    $payload = [
        'game_id' => 'test-game-id',
        'type' => 'soluce',
        'title' => 'How to Beat Ganon',
        'body' => 'Step-by-step guide to defeating Ganon.',
        'visitor_id' => 'test-visitor-id'
    ];
    
    $this->app->request->shouldReceive('getJsonRawBody')
        ->once()
        ->with(true)
        ->andReturn($payload);
    
    // Mock game finding
    $gameInstance = m::mock(Game::class);
    $this->gameMock->shouldReceive('findFirst')
        ->once()
        ->andReturn($gameInstance);
        
    // Mock visitor finding
    $visitorInstance = m::mock(Visitor::class);
    $this->visitorMock->shouldReceive('findFirst')
        ->once()
        ->andReturn($visitorInstance);
    
    // Mock Content instance and saving
    $contentInstance = m::mock(Content::class);
    $contentInstance->shouldReceive('save')->once()->andReturn(true);
    
    // Set properties that will be returned in the response
    $contentInstance->id = 'test-content-id';
    $contentInstance->type = $payload['type'];
    $contentInstance->game_id = $payload['game_id'];
    $contentInstance->visitor_id = $payload['visitor_id'];
    $contentInstance->title = $payload['title'];
    $contentInstance->body = $payload['body'];
    $contentInstance->status = 'draft';
    $contentInstance->created_at = '2023-12-31 23:59:59';
    
    // Ensure the Content constructor is mocked
    $this->contentMock->shouldReceive('__construct')->andReturn($contentInstance);
    $this->contentMock->shouldReceive('__set')->andReturnSelf();
    
    // Allow Content instantiation
    m::mock('alias:App\Domain\Model\Content')
        ->shouldReceive('__construct')
        ->andReturn($contentInstance);
    
    // Act
    $response = $this->controller->createAction();
    $content = json_decode($response->getContent(), true);
    
    // Assert
    expect($response->getStatusCode())->toBe(201);
    expect($content)->toHaveKey('id');
    expect($content)->toHaveKey('type');
    expect($content)->toHaveKey('game_id');
    expect($content)->toHaveKey('visitor_id');
    expect($content)->toHaveKey('title');
    expect($content)->toHaveKey('body');
    expect($content)->toHaveKey('status');
    expect($content)->toHaveKey('created_at');
    expect($content['id'])->toBe('test-content-id');
    expect($content['type'])->toBe('soluce');
    expect($content['status'])->toBe('draft');
});

test('createAction validates required fields', function () {
    // Arrange - Mock the request with missing fields
    $payload = [
        'game_id' => 'test-game-id',
        // missing type
        'title' => 'How to Beat Ganon',
        // missing body
    ];
    
    $this->app->request->shouldReceive('getJsonRawBody')
        ->once()
        ->with(true)
        ->andReturn($payload);
    
    // Act
    $response = $this->controller->createAction();
    $content = json_decode($response->getContent(), true);
    
    // Assert
    expect($response->getStatusCode())->toBe(400);
    expect($content)->toHaveKey('error');
    expect($content)->toHaveKey('message');
    expect($content['error'])->toBe('Error');
    expect($content['message'])->toBe('Game ID, type, title, and body are required fields');
});

test('listAction returns paginated content list', function () {
    // Arrange - Mock the request params
    $this->app->request->shouldReceive('getQuery')
        ->with('page', 'int', 1)
        ->andReturn(1);
    
    $this->app->request->shouldReceive('getQuery')
        ->with('limit', 'int', 20)
        ->andReturn(10);
    
    $this->app->request->shouldReceive('getQuery')
        ->with('status', 'string')
        ->andReturn('published');
    
    // Mock Content::count and Content::find
    $this->contentMock->shouldReceive('count')
        ->once()
        ->andReturn(25);
    
    // Create mock content collection
    $contentCollection = [];
    for ($i = 1; $i <= 10; $i++) {
        $contentInstance = m::mock(Content::class);
        $contentInstance->id = "content-id-{$i}";
        $contentInstance->type = 'soluce';
        $contentInstance->game_id = 'game-id';
        $contentInstance->visitor_id = 'visitor-id';
        $contentInstance->title = "Content Title {$i}";
        $contentInstance->body = "Content Body {$i}";
        $contentInstance->status = 'published';
        $contentInstance->created_at = '2023-12-31 23:59:59';
        $contentInstance->updated_at = null;
        
        $contentCollection[] = $contentInstance;
    }
    
    $this->contentMock->shouldReceive('find')
        ->once()
        ->andReturn($contentCollection);
    
    // Act
    $response = $this->controller->listAction();
    $content = json_decode($response->getContent(), true);
    
    // Assert
    expect($response->getStatusCode())->toBe(200);
    expect($content)->toHaveKey('data');
    expect($content)->toHaveKey('meta');
    expect($content['meta'])->toHaveKey('page');
    expect($content['meta'])->toHaveKey('limit');
    expect($content['meta'])->toHaveKey('total');
    expect($content['meta']['page'])->toBe(1);
    expect($content['meta']['limit'])->toBe(10);
    expect($content['meta']['total'])->toBe(25);
    expect(count($content['data']))->toBe(10);
});

test('moderateAction updates content status', function () {
    // Arrange - Set the content ID and payload
    $contentId = 'test-content-id';
    $payload = [
        'status' => 'published'
    ];
    
    $this->app->request->shouldReceive('getJsonRawBody')
        ->once()
        ->with(true)
        ->andReturn($payload);
    
    // Mock content finding
    $contentInstance = m::mock(Content::class);
    $contentInstance->shouldReceive('save')->once()->andReturn(true);
    
    // Set properties that will be returned in the response
    $contentInstance->id = $contentId;
    $contentInstance->status = $payload['status'];
    $contentInstance->updated_at = '2023-12-31 23:59:59';
    
    $this->contentMock->shouldReceive('findFirst')
        ->once()
        ->andReturn($contentInstance);
    
    // Act
    $response = $this->controller->moderateAction($contentId);
    $content = json_decode($response->getContent(), true);
    
    // Assert
    expect($response->getStatusCode())->toBe(200);
    expect($content)->toHaveKey('id');
    expect($content)->toHaveKey('status');
    expect($content)->toHaveKey('updated_at');
    expect($content['id'])->toBe($contentId);
    expect($content['status'])->toBe('published');
}); 