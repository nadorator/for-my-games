<?php

namespace App\Services\Content;

use App\Domain\Model\Content;
use App\Domain\Model\Game;
use App\Domain\Model\Visitor;
use Phalcon\Mvc\Micro;

class ContentController
{
    /**
     * @var Micro
     */
    private $app;

    /**
     * Constructor
     */
    public function __construct(Micro $app)
    {
        $this->app = $app;
    }

    /**
     * List all contents (paginated)
     * GET /contents
     */
    public function listAction()
    {
        try {
            $page = $this->app->request->getQuery('page', 'int', 1);
            $limit = $this->app->request->getQuery('limit', 'int', 20);
            $status = $this->app->request->getQuery('status', 'string');

            // Calculate offset
            $offset = ($page - 1) * $limit;

            // Build query
            $conditions = [];
            $parameters = [];

            if ($status) {
                $conditions[] = 'status = :status:';
                $parameters['status'] = $status;
            }

            $conditionString = count($conditions) > 0 ? implode(' AND ', $conditions) : '';

            // Get total count
            $total = Content::count([
                'conditions' => $conditionString,
                'bind' => $parameters
            ]);

            // Get paginated results
            $contents = Content::find([
                'conditions' => $conditionString,
                'bind' => $parameters,
                'limit' => $limit,
                'offset' => $offset
            ]);

            $result = [];
            foreach ($contents as $content) {
                $result[] = [
                    'id' => $content->id,
                    'type' => $content->type,
                    'game_id' => $content->game_id,
                    'visitor_id' => $content->visitor_id,
                    'title' => $content->title,
                    'body' => $content->body,
                    'status' => $content->status,
                    'created_at' => $content->created_at,
                    'updated_at' => $content->updated_at
                ];
            }

            return $this->app->response->setJsonContent([
                'data' => $result,
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total
                ]
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error retrieving contents: ' . $e->getMessage());
        }
    }

    /**
     * Get a content
     * GET /contents/{content_id}
     * 
     * @param string $content_id Content ID
     */
    public function getAction(string $content_id)
    {
        try {
            $content = Content::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $content_id
                ]
            ]);

            if (!$content) {
                return $this->sendErrorResponse('Content not found', 404);
            }

            return $this->app->response->setJsonContent([
                'id' => $content->id,
                'type' => $content->type,
                'game_id' => $content->game_id,
                'visitor_id' => $content->visitor_id,
                'title' => $content->title,
                'body' => $content->body,
                'status' => $content->status,
                'created_at' => $content->created_at,
                'updated_at' => $content->updated_at
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error retrieving content: ' . $e->getMessage());
        }
    }

    /**
     * Create a new content
     * POST /contents
     */
    public function createAction()
    {
        try {
            $payload = $this->app->request->getJsonRawBody(true);
            
            // Validate required fields
            if (!isset($payload['game_id']) || !isset($payload['type']) || !isset($payload['title']) || !isset($payload['body'])) {
                return $this->sendErrorResponse('Game ID, type, title, and body are required fields');
            }

            // Validate type
            if (!in_array($payload['type'], ['test', 'configuration', 'soluce'])) {
                return $this->sendErrorResponse('Type must be one of: test, configuration, soluce');
            }

            // Validate game exists
            $game = Game::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $payload['game_id']
                ]
            ]);

            if (!$game) {
                return $this->sendErrorResponse('Game not found', 404);
            }

            // Validate visitor exists if provided
            if (isset($payload['visitor_id'])) {
                $visitor = Visitor::findFirst([
                    'conditions' => 'id = :id:',
                    'bind' => [
                        'id' => $payload['visitor_id']
                    ]
                ]);

                if (!$visitor) {
                    return $this->sendErrorResponse('Visitor not found', 404);
                }
            }

            $content = new Content();
            $content->type = $payload['type'];
            $content->game_id = $payload['game_id'];
            $content->visitor_id = $payload['visitor_id'] ?? null;
            $content->title = $payload['title'];
            $content->body = $payload['body'];

            if ($content->save() === false) {
                return $this->sendErrorResponse($content->getMessages());
            }

            return $this->app->response->setJsonContent([
                'id' => $content->id,
                'type' => $content->type,
                'game_id' => $content->game_id,
                'visitor_id' => $content->visitor_id,
                'title' => $content->title,
                'body' => $content->body,
                'status' => $content->status,
                'created_at' => $content->created_at
            ])->setStatusCode(201);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error creating content: ' . $e->getMessage());
        }
    }

    /**
     * Update a content
     * PUT /contents/{content_id}
     * 
     * @param string $content_id Content ID
     */
    public function updateAction(string $content_id)
    {
        try {
            $payload = $this->app->request->getJsonRawBody(true);
            
            // Validate visitor_id is provided
            if (!isset($payload['visitor_id'])) {
                return $this->sendErrorResponse('Visitor ID is required');
            }

            // Find content
            $content = Content::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $content_id
                ]
            ]);

            if (!$content) {
                return $this->sendErrorResponse('Content not found', 404);
            }

            // Verify visitor owns the content
            if ($content->visitor_id !== $payload['visitor_id']) {
                return $this->sendErrorResponse('You do not have permission to update this content', 403);
            }

            // Update fields
            if (isset($payload['title'])) {
                $content->title = $payload['title'];
            }

            if (isset($payload['body'])) {
                $content->body = $payload['body'];
            }

            // Reset status to draft when updated
            $content->status = 'draft';

            if ($content->save() === false) {
                return $this->sendErrorResponse($content->getMessages());
            }

            return $this->app->response->setJsonContent([
                'id' => $content->id,
                'type' => $content->type,
                'game_id' => $content->game_id,
                'visitor_id' => $content->visitor_id,
                'title' => $content->title,
                'body' => $content->body,
                'status' => $content->status,
                'created_at' => $content->created_at,
                'updated_at' => $content->updated_at
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error updating content: ' . $e->getMessage());
        }
    }

    /**
     * Moderate a content
     * PATCH /contents/{content_id}/moderate
     * 
     * @param string $content_id Content ID
     */
    public function moderateAction(string $content_id)
    {
        try {
            $payload = $this->app->request->getJsonRawBody(true);
            
            // Validate status is provided
            if (!isset($payload['status'])) {
                return $this->sendErrorResponse('Status is required');
            }

            // Validate status
            if (!in_array($payload['status'], ['draft', 'published', 'moderated'])) {
                return $this->sendErrorResponse('Status must be one of: draft, published, moderated');
            }

            // Find content
            $content = Content::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $content_id
                ]
            ]);

            if (!$content) {
                return $this->sendErrorResponse('Content not found', 404);
            }

            // Update status
            $content->status = $payload['status'];

            if ($content->save() === false) {
                return $this->sendErrorResponse($content->getMessages());
            }

            return $this->app->response->setJsonContent([
                'id' => $content->id,
                'status' => $content->status,
                'updated_at' => $content->updated_at
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error moderating content: ' . $e->getMessage());
        }
    }

    /**
     * Send error response
     * 
     * @param mixed $message
     * @param int $statusCode
     */
    private function sendErrorResponse($message, int $statusCode = 400)
    {
        $response = $this->app->response;
        $response->setStatusCode($statusCode);
        
        if (is_array($message)) {
            $errors = [];
            foreach ($message as $msg) {
                $errors[] = $msg->getMessage();
            }
            $response->setJsonContent([
                'error' => 'Validation Error',
                'message' => $errors
            ]);
        } else {
            $response->setJsonContent([
                'error' => 'Error',
                'message' => $message
            ]);
        }
        
        return $response;
    }
} 