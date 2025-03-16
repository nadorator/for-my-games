<?php

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Auth\JwtService;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var JwtService
     */
    private $jwtService;

    /**
     * Constructor
     */
    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Execute the middleware
     *
     * @param Micro $app
     * @return bool
     */
    public function call(Micro $app)
    {
        // Check if authorization header exists
        $authHeader = $app->request->getHeader('Authorization');
        if (!$authHeader) {
            $this->sendUnauthorizedResponse($app, 'Authorization header not found');
            return false;
        }

        // Check if it's a bearer token
        if (!str_starts_with($authHeader, 'Bearer ')) {
            $this->sendUnauthorizedResponse($app, 'Bearer token not found');
            return false;
        }

        // Extract token
        $token = substr($authHeader, 7);
        
        // Validate token
        $payload = $this->jwtService->validateToken($token);
        if (!$payload) {
            $this->sendUnauthorizedResponse($app, 'Invalid or expired token');
            return false;
        }

        // Store editor data in request
        $app['editor'] = [
            'id' => $payload->editor_id,
            'role' => $payload->role
        ];

        return true;
    }

    /**
     * Send unauthorized response
     *
     * @param Micro $app
     * @param string $message
     */
    private function sendUnauthorizedResponse(Micro $app, string $message): void
    {
        $app->response->setStatusCode(401, 'Unauthorized');
        $app->response->setJsonContent([
            'error' => 'Unauthorized',
            'message' => $message
        ]);
        $app->response->send();
    }
} 