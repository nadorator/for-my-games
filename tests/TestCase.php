<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use App\Infrastructure\Auth\JwtService;
use App\Infrastructure\Middleware\AuthMiddleware;
use Phalcon\Db\Adapter\Pdo\Mysql;

class TestCase extends BaseTestCase
{
    /**
     * @var Micro
     */
    protected $app;

    /**
     * @var FactoryDefault
     */
    protected $di;

    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create DI container
        $this->di = new FactoryDefault();

        // Set up a dummy database service (with SQLite in-memory)
        $this->di->setShared('db', function () {
            return new Mysql([
                'host'     => 'localhost',
                'username' => 'test',
                'password' => 'test',
                'dbname'   => 'test',
                'charset'  => 'utf8mb4',
                // We'll actually mock this in the tests
            ]);
        });

        // Set up the JWT service
        $this->di->setShared('jwtService', function () {
            return new JwtService('test_secret', 3600);
        });

        // Set up the auth middleware
        $this->di->setShared('authMiddleware', function () {
            $jwtService = $this->get('jwtService');
            return new AuthMiddleware($jwtService);
        });

        // Create and return the application
        $this->app = new Micro($this->di);
    }

    /**
     * Clean up the test environment
     */
    protected function tearDown(): void
    {
        $this->app = null;
        $this->di = null;
        parent::tearDown();
    }
} 