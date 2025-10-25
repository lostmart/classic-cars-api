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
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 

// Make database available globally
$GLOBALS['db'] = $db;


// Auto-seed database if empty
function isDatabaseEmpty(PDO $db): bool
{
    try {
        // Check if users table exists and has data
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        return $result['count'] == 0;
    } catch (PDOException $e) {
        // Table doesn't exist, database is empty
        return true;
    }
}

if (isDatabaseEmpty($db)) {
    echo "Database is empty. Auto-seeding...\n";
    require __DIR__ . '/../database/seed.php';
    echo "Auto-seeding completed!\n\n";
}

// Load routes
require __DIR__ . '/../src/routes/routes.php';
require __DIR__ . '/../src/routes/tours.php';
require __DIR__ . '/../src/routes/cars.php'; 
require __DIR__ . '/../src/routes/bookings.php';
require __DIR__ . '/../src/routes/users.php'; 

// Run app
$app->run();

/**************************************************************
The database will be automatically initialized on first run with:

- Table creation
- Seed data (users, cars, tours, sample bookings)

***************************************************************/