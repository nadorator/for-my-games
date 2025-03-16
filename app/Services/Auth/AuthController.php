<?php

namespace App\Services\Auth;

use App\Domain\Model\Editor;
use App\Infrastructure\Auth\JwtService;
use Phalcon\Mvc\Micro;

class AuthController
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
     * Login action
     * POST /auth/login
     */
    public function loginAction()
    {
        try {
            $payload = $this->app->request->getJsonRawBody(true);
            
            // Validate required fields
            if (!isset($payload['email']) || !isset($payload['password'])) {
                return $this->sendErrorResponse('Email and password are required fields');
            }

            // Find editor by email
            $editor = Editor::findFirst([
                'conditions' => 'email = :email:',
                'bind' => [
                    'email' => $payload['email']
                ]
            ]);

            if (!$editor) {
                return $this->sendErrorResponse('Invalid credentials', 401);
            }

            // Validate password
            if (!$editor->validateCredentials($payload['password'])) {
                return $this->sendErrorResponse('Invalid credentials', 401);
            }

            // Generate JWT token
            $jwtService = $this->app->getDI()->get('jwtService');
            $token = $jwtService->generateToken($editor->id, $editor->role);

            return $this->app->response->setJsonContent([
                'token' => $token,
                'expires_at' => $jwtService->getExpirationDate()
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error during login: ' . $e->getMessage());
        }
    }

    /**
     * Refresh token action
     * POST /auth/refresh
     */
    public function refreshAction()
    {
        try {
            // Editor data is already validated by middleware
            $editorData = $this->app['editor'];
            
            // Generate new JWT token
            $jwtService = $this->app->getDI()->get('jwtService');
            $token = $jwtService->generateToken($editorData['id'], $editorData['role']);

            return $this->app->response->setJsonContent([
                'token' => $token,
                'expires_at' => $jwtService->getExpirationDate()
            ]);
        } catch (\Exception $e) {
            return $this->sendErrorResponse('Error refreshing token: ' . $e->getMessage());
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