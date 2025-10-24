<?php

declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create Container
$container = new Container();

// Set container to create App with on AppFactory
AppFactory::setContainer($container);

// Create App
$app = AppFactory::create();

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware(
    $_ENV['APP_DEBUG'] === 'true',
    true,
    true
);

// Load dependencies (database, services, etc.)
require __DIR__ . '/../bootstrap/dependencies.php';

// Load middleware
require __DIR__ . '/../bootstrap/middleware.php';

// Load routes
require __DIR__ . '/../src/routes/routes.php';

// Run app
$app->run();