<?php

use App\Services\Content\ContentService;

// Include composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Get the application
$app = require_once __DIR__ . '/../app/bootstrap.php';

// Set up CORS
$app->before(function () use ($app) {
    $origin = $app->request->getHeader('ORIGIN') ?: '*';
    $app->response->setHeader('Access-Control-Allow-Origin', $origin)
        ->setHeader('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE,OPTIONS,PATCH')
        ->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization')
        ->setHeader('Access-Control-Allow-Credentials', 'true');
        
    if ($app->request->getMethod() == 'OPTIONS') {
        $app->response->setStatusCode(200, 'OK')->send();
        exit;
    }
    
    return true;
});

// Initialize the Content Service
new ContentService($app);

// Set up not found handler
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, 'Not Found');
    $app->response->setJsonContent([
        'error' => 'Not Found',
        'message' => 'The requested endpoint was not found'
    ]);
    $app->response->send();
});

// Handle exceptions
$app->error(function ($exception) use ($app) {
    $app->response->setStatusCode(500, 'Internal Server Error');
    $app->response->setJsonContent([
        'error' => 'Internal Server Error',
        'message' => $exception->getMessage()
    ]);
    $app->response->send();
});

// Handle the request
$app->handle(); 