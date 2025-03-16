<?php

use App\Infrastructure\Auth\JwtService;
use App\Infrastructure\Middleware\AuthMiddleware;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Mvc\Micro;

// Create DI container
$di = new FactoryDefault();

// Set up the database service
$di->setShared('db', function () {
    return new Mysql([
        'host'     => 'mariadb',
        'username' => getenv('MYSQL_USER'),
        'password' => getenv('MYSQL_PASSWORD'),
        'dbname'   => getenv('MYSQL_DATABASE'),
        'charset'  => 'utf8mb4',
    ]);
});

// Set up the JWT service
$di->setShared('jwtService', function () {
    $secret = getenv('JWT_SECRET');
    $expiration = (int) getenv('JWT_EXPIRATION');
    
    return new JwtService($secret, $expiration);
});

// Set up the auth middleware
$di->setShared('authMiddleware', function () {
    $jwtService = $this->get('jwtService');
    
    return new AuthMiddleware($jwtService);
});

// Create and return the application
return new Micro($di); 