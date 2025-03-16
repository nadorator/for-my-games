<?php

namespace App\Services\User;

use App\Domain\Model\Editor;
use Phalcon\Mvc\Micro;

class EditorController
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
     * List all editors
     * GET /editors
     */
    public function listAction()
    {
        try {
            $editors = Editor::find();
            $result = [];

            foreach ($editors as $editor) {
                $result[] = [
                    'id' => $editor->id,
                    'email' => $editor->email,
                    'role' => $editor->role,
                    'created_at' => $editor->created_at
                ];
            }

            return $this->app->response->setJsonContent($result);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error retrieving editors: ' . $e->getMessage());
        }
    }

    /**
     * Create a new editor
     * POST /editors
     */
    public function createAction()
    {
        try {
            $payload = $this->app->request->getJsonRawBody(true);
            
            // Validate required fields
            if (!isset($payload['email']) || !isset($payload['password']) || !isset($payload['role'])) {
                return $this->sendErrorResponse('Email, password, and role are required fields');
            }

            // Validate role
            if (!in_array($payload['role'], ['admin', 'moderator'])) {
                return $this->sendErrorResponse('Role must be either admin or moderator');
            }

            $editor = new Editor();
            $editor->email = $payload['email'];
            $editor->password = $payload['password'];
            $editor->role = $payload['role'];

            if ($editor->save() === false) {
                return $this->sendErrorResponse($editor->getMessages());
            }

            return $this->app->response->setJsonContent([
                'id' => $editor->id,
                'email' => $editor->email,
                'role' => $editor->role,
                'created_at' => $editor->created_at
            ])->setStatusCode(201);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error creating editor: ' . $e->getMessage());
        }
    }

    /**
     * Delete an editor
     * DELETE /editors/{editor_id}
     * 
     * @param string $editor_id Editor ID
     */
    public function deleteAction(string $editor_id)
    {
        try {
            $editor = Editor::findFirst([
                'conditions' => 'id = :id:',
                'bind' => [
                    'id' => $editor_id
                ]
            ]);

            if (!$editor) {
                return $this->sendErrorResponse('Editor not found', 404);
            }

            if (!$editor->delete()) {
                return $this->sendErrorResponse($editor->getMessages());
            }

            return $this->app->response->setStatusCode(204);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error deleting editor: ' . $e->getMessage());
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