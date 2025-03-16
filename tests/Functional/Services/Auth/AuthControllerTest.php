<?php

use App\Services\Auth\AuthController;
use App\Domain\Model\Editor;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Mockery as m;

beforeEach(function () {
    $this->app = getDI()->get('app');
    $this->app->request = m::mock('Phalcon\Http\Request');
    $this->app->response = new Response();
    
    // Create the controller
    $this->controller = new AuthController($this->app);
    
    // Mock editor finder
    $this->editorMock = m::mock('overload:App\Domain\Model\Editor');
});

afterEach(function () {
    m::close();
});

test('loginAction returns JWT token on successful authentication', function () {
    // Arrange - Mock the request with valid credentials
    $payload = [
        'email' => 'admin@example.com',
        'password' => 'password'
    ];
    
    $this->app->request->shouldReceive('getJsonRawBody')
        ->once()
        ->with(true)
        ->andReturn($payload);
    
    // Mock editor finding and validation
    $editorInstance = m::mock(Editor::class);
    $editorInstance->shouldReceive('validateCredentials')
        ->once()
        ->with($payload['password'])
        ->andReturn(true);
    
    $editorInstance->id = 'test-editor-id';
    $editorInstance->role = 'admin';
    
    $this->editorMock->shouldReceive('findFirst')
        ->once()
        ->andReturn($editorInstance);
    
    // Mock JWT service
    $jwtService = m::mock('App\Infrastructure\Auth\JwtService');
    $jwtService->shouldReceive('generateToken')
        ->once()
        ->with($editorInstance->id, $editorInstance->role)
        ->andReturn('mock.jwt.token');
    
    $jwtService->shouldReceive('getExpirationDate')
        ->once()
        ->andReturn('2023-12-31T23:59:59Z');
    
    // Add JWT service to DI container
    $this->app->getDI()->setShared('jwtService', function () use ($jwtService) {
        return $jwtService;
    });
    
    // Act
    $response = $this->controller->loginAction();
    $content = json_decode($response->getContent(), true);
    
    // Assert
    expect($response->getStatusCode())->toBe(200);
    expect($content)->toHaveKey('token');
    expect($content)->toHaveKey('expires_at');
    expect($content['token'])->toBe('mock.jwt.token');
    expect($content['expires_at'])->toBe('2023-12-31T23:59:59Z');
});

test('loginAction returns 401 with invalid credentials', function () {
    // Arrange - Mock the request with invalid credentials
    $payload = [
        'email' => 'admin@example.com',
        'password' => 'wrong_password'
    ];
    
    $this->app->request->shouldReceive('getJsonRawBody')
        ->once()
        ->with(true)
        ->andReturn($payload);
    
    // Mock editor finding
    $editorInstance = m::mock(Editor::class);
    $editorInstance->shouldReceive('validateCredentials')
        ->once()
        ->with($payload['password'])
        ->andReturn(false);
    
    $this->editorMock->shouldReceive('findFirst')
        ->once()
        ->andReturn($editorInstance);
    
    // Act
    $response = $this->controller->loginAction();
    $content = json_decode($response->getContent(), true);
    
    // Assert
    expect($response->getStatusCode())->toBe(401);
    expect($content)->toHaveKey('error');
    expect($content)->toHaveKey('message');
    expect($content['error'])->toBe('Error');
    expect($content['message'])->toBe('Invalid credentials');
});

test('loginAction returns 400 with missing fields', function () {
    // Arrange - Mock the request with missing fields
    $payload = [
        'email' => 'admin@example.com'
        // Missing password
    ];
    
    $this->app->request->shouldReceive('getJsonRawBody')
        ->once()
        ->with(true)
        ->andReturn($payload);
    
    // Act
    $response = $this->controller->loginAction();
    $content = json_decode($response->getContent(), true);
    
    // Assert
    expect($response->getStatusCode())->toBe(400);
    expect($content)->toHaveKey('error');
    expect($content)->toHaveKey('message');
    expect($content['error'])->toBe('Error');
    expect($content['message'])->toBe('Email and password are required fields');
});

test('refreshAction returns new JWT token', function () {
    // Arrange - Set up editor data in the app
    $this->app['editor'] = [
        'id' => 'test-editor-id',
        'role' => 'admin'
    ];
    
    // Mock JWT service
    $jwtService = m::mock('App\Infrastructure\Auth\JwtService');
    $jwtService->shouldReceive('generateToken')
        ->once()
        ->with('test-editor-id', 'admin')
        ->andReturn('new.jwt.token');
    
    $jwtService->shouldReceive('getExpirationDate')
        ->once()
        ->andReturn('2023-12-31T23:59:59Z');
    
    // Add JWT service to DI container
    $this->app->getDI()->setShared('jwtService', function () use ($jwtService) {
        return $jwtService;
    });
    
    // Act
    $response = $this->controller->refreshAction();
    $content = json_decode($response->getContent(), true);
    
    // Assert
    expect($response->getStatusCode())->toBe(200);
    expect($content)->toHaveKey('token');
    expect($content)->toHaveKey('expires_at');
    expect($content['token'])->toBe('new.jwt.token');
    expect($content['expires_at'])->toBe('2023-12-31T23:59:59Z');
}); 