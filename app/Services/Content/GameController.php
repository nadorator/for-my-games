<?php

namespace App\Services\Content;

use App\Domain\Model\Game;
use Phalcon\Mvc\Micro;

class GameController
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
     * List all games
     * GET /games
     */
    public function listAction()
    {
        try {
            $games = Game::find();
            $result = [];

            foreach ($games as $game) {
                $result[] = [
                    'id' => $game->id,
                    'title' => $game->title,
                    'description' => $game->description,
                    'created_at' => $game->created_at
                ];
            }

            return $this->app->response->setJsonContent($result);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error retrieving games: ' . $e->getMessage());
        }
    }

    /**
     * Get a game
     * GET /games/{game_id}
     * 
     * @param string $game_id Game ID
     */
    public function getAction(string $game_id)
    {
        try {
            $game = Game::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $game_id
                ]
            ]);

            if (!$game) {
                return $this->sendErrorResponse('Game not found', 404);
            }

            return $this->app->response->setJsonContent([
                'id' => $game->id,
                'title' => $game->title,
                'description' => $game->description,
                'created_at' => $game->created_at
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error retrieving game: ' . $e->getMessage());
        }
    }

    /**
     * Create a new game
     * POST /games
     */
    public function createAction()
    {
        try {
            $payload = $this->app->request->getJsonRawBody(true);
            
            // Validate required fields
            if (!isset($payload['title'])) {
                return $this->sendErrorResponse('Title is a required field');
            }

            $game = new Game();
            $game->title = $payload['title'];
            $game->description = $payload['description'] ?? null;

            if ($game->save() === false) {
                return $this->sendErrorResponse($game->getMessages());
            }

            return $this->app->response->setJsonContent([
                'id' => $game->id,
                'title' => $game->title,
                'description' => $game->description,
                'created_at' => $game->created_at
            ])->setStatusCode(201);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error creating game: ' . $e->getMessage());
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