<?php

namespace App\Infrastructure\Middleware;

use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class RoleMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    private $allowedRoles;

    /**
     * Constructor
     * 
     * @param array $allowedRoles
     */
    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    /**
     * Execute the middleware
     *
     * @param Micro $app
     * @return bool
     */
    public function call(Micro $app)
    {
        // Check if editor data is present
        if (!isset($app['editor'])) {
            $this->sendForbiddenResponse($app, 'Authentication required');
            return false;
        }

        $editorRole = $app['editor']['role'];

        // Check if editor role is allowed
        if (!in_array($editorRole, $this->allowedRoles)) {
            $this->sendForbiddenResponse($app, 'Insufficient permissions');
            return false;
        }

        return true;
    }

    /**
     * Send forbidden response
     *
     * @param Micro $app
     * @param string $message
     */
    private function sendForbiddenResponse(Micro $app, string $message): void
    {
        $app->response->setStatusCode(403, 'Forbidden');
        $app->response->setJsonContent([
            'error' => 'Forbidden',
            'message' => $message
        ]);
        $app->response->send();
    }
} 