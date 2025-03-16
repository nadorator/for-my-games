<?php

namespace App\Services\Content;

use App\Infrastructure\Middleware\AuthMiddleware;
use App\Infrastructure\Middleware\RoleMiddleware;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

class ContentService
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
        // Game endpoints (unauthenticated)
        $gameCollection = new MicroCollection();
        $gameCollection
            ->setHandler(new GameController($this->app))
            ->setPrefix('/v1/games');

        $gameCollection->get('/', 'listAction');
        $gameCollection->get('/{game_id}', 'getAction');
        $gameCollection->post('/', 'createAction');

        $this->app->mount($gameCollection);

        // Content endpoints (mixed authentication)
        $contentCollection = new MicroCollection();
        $contentCollection
            ->setHandler(new ContentController($this->app))
            ->setPrefix('/v1/contents');

        // Unauthenticated endpoints
        $contentCollection->post('/', 'createAction');
        $contentCollection->get('/{content_id}', 'getAction');
        $contentCollection->put('/{content_id}', 'updateAction');

        // Authenticated endpoints
        $contentCollection->get('/', 'listAction');
        $contentCollection->patch('/{content_id}/moderate', 'moderateAction');

        // Add auth middleware for editor-only endpoints
        $authMiddleware = $this->app->getDI()->get('authMiddleware');
        $this->app->before(function () use ($authMiddleware) {
            $path = $this->app->request->getURI();
            $method = $this->app->request->getMethod();
            
            if (
                ($path === '/v1/contents' && $method === 'GET') ||
                (strpos($path, '/v1/contents/') !== false && strpos($path, '/moderate') !== false && $method === 'PATCH')
            ) {
                return $authMiddleware->call($this->app);
            }
            return true;
        });

        // Add role middleware for moderation (both admin and moderator can moderate)
        $moderatorMiddleware = new RoleMiddleware(['admin', 'moderator']);
        $this->app->before(function () use ($moderatorMiddleware) {
            $path = $this->app->request->getURI();
            $method = $this->app->request->getMethod();
            
            if (strpos($path, '/v1/contents/') !== false && strpos($path, '/moderate') !== false && $method === 'PATCH') {
                return $moderatorMiddleware->call($this->app);
            }
            return true;
        });

        $this->app->mount($contentCollection);
    }
} 