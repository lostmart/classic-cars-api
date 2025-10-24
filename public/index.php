<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

// Load environment variables only if .env file exists
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Create app
$app = AppFactory::create();

// Add body parsing middleware
$app->addBodyParsingMiddleware();

// Add routing middleware
$app->addRoutingMiddleware();

// Add error middleware
$errorMiddleware = $app->addErrorMiddleware(
    $_ENV['APP_DEBUG'] ?? true,
    true,
    true
);

// Create database connection
$dbPath = __DIR__ . '/../' . ($_ENV['DB_PATH'] ?? 'database/database.sqlite');
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Make database available globally
$GLOBALS['db'] = $db;

// Load routes
require __DIR__ . '/../src/routes/routes.php';
require __DIR__ . '/../src/routes/tours.php';

// Run app
$app->run();