<?php

namespace App\Services\User;

use App\Domain\Model\Editor;
use App\Domain\Model\Visitor;
use App\Infrastructure\Middleware\AuthMiddleware;
use App\Infrastructure\Middleware\RoleMiddleware;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

class UserService
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
        // Visitor endpoints (unauthenticated)
        $visitorCollection = new MicroCollection();
        $visitorCollection
            ->setHandler(new VisitorController($this->app))
            ->setPrefix('/v1/visitors');

        $visitorCollection->post('/', 'createAction');
        $visitorCollection->get('/{visitor_id}', 'getAction');

        $this->app->mount($visitorCollection);

        // Editor endpoints (authenticated)
        $editorCollection = new MicroCollection();
        $editorCollection
            ->setHandler(new EditorController($this->app))
            ->setPrefix('/v1/editors');

        $editorCollection->get('/', 'listAction');
        $editorCollection->post('/', 'createAction');
        $editorCollection->delete('/{editor_id}', 'deleteAction');

        // Add auth middleware for editor endpoints
        $authMiddleware = $this->app->getDI()->get('authMiddleware');
        $this->app->before(function () use ($authMiddleware) {
            $path = $this->app->request->getURI();
            if (strpos($path, '/v1/editors') === 0) {
                return $authMiddleware->call($this->app);
            }
            return true;
        });

        // Add role middleware for admin-only operations
        $adminMiddleware = new RoleMiddleware(['admin']);
        $this->app->before(function () use ($adminMiddleware) {
            $path = $this->app->request->getURI();
            $method = $this->app->request->getMethod();
            
            // Admin-only operations
            if (
                ($path === '/v1/editors' && ($method === 'GET' || $method === 'POST')) ||
                (strpos($path, '/v1/editors/') === 0 && $method === 'DELETE')
            ) {
                return $adminMiddleware->call($this->app);
            }
            return true;
        });

        $this->app->mount($editorCollection);
    }
} 