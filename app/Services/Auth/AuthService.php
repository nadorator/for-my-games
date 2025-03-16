<?php

namespace App\Services\Auth;

use App\Infrastructure\Middleware\AuthMiddleware;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

class AuthService
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
        $this->registerRoutes();
    }

    /**
     * Register routes
     */
    private function registerRoutes(): void
    {
        // Auth endpoints
        $authCollection = new MicroCollection();
        $authCollection
            ->setHandler(new AuthController($this->app))
            ->setPrefix('/v1/auth');

        $authCollection->post('/login', 'loginAction');
        $authCollection->post('/refresh', 'refreshAction');

        // Add auth middleware for refresh endpoint
        $authMiddleware = $this->app->getDI()->get('authMiddleware');
        $this->app->before(function () use ($authMiddleware) {
            $path = $this->app->request->getURI();
            if ($path === '/v1/auth/refresh') {
                return $authMiddleware->call($this->app);
            }
            return true;
        });

        $this->app->mount($authCollection);
    }
} 