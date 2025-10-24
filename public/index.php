<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

// Load environment variables - NEW!
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Create app
$app = AppFactory::create();

// Add routing middleware
$app->addRoutingMiddleware();

// Add error middleware to see errors clearly
$app->addErrorMiddleware(true, true, true);

// Create database connection - NEW!
$dbPath = __DIR__ . '/../' . $_ENV['DB_PATH'];
$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$GLOBALS['db'] = $db;

// Load routes from separate file
require __DIR__ . '/../src/routes/routes.php';
require __DIR__ . '/../src/routes/tours.php';  // tours routes

// Run app
$app->run();