<?php

declare(strict_types=1);

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('UTC');

// Define test environment constants if not already defined
if (!defined('APP_ENV')) {
    define('APP_ENV', 'testing');
}

// Create test database connection
if (!defined('TEST_DB_PATH')) {
    define('TEST_DB_PATH', ':memory:');
}

// Helper function to create a test database
function createTestDatabase(): PDO {
    $pdo = new PDO('sqlite:' . TEST_DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cars (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            make VARCHAR(50) NOT NULL,
            model VARCHAR(50) NOT NULL,
            year INTEGER NOT NULL,
            color VARCHAR(30),
            capacity INTEGER NOT NULL,
            license_plate VARCHAR(20) UNIQUE NOT NULL,
            driver_id INTEGER,
            status VARCHAR(20) DEFAULT 'available',
            image_urls TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (driver_id) REFERENCES users(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            external_user_id VARCHAR(255) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            role VARCHAR(20) NOT NULL,
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tours (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            duration_hours INTEGER NOT NULL,
            base_price DECIMAL(10,2) NOT NULL,
            price_per_person DECIMAL(10,2) NOT NULL,
            max_participants INTEGER NOT NULL,
            includes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            booking_number VARCHAR(50) UNIQUE NOT NULL,
            customer_id INTEGER NOT NULL,
            car_id INTEGER,
            tour_id INTEGER,
            pickup_location VARCHAR(255) NOT NULL,
            dropoff_location VARCHAR(255) NOT NULL,
            booking_date DATE NOT NULL,
            booking_time TIME NOT NULL,
            duration_hours INTEGER NOT NULL,
            total_passengers INTEGER NOT NULL,
            special_requests TEXT,
            total_price DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            payment_status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES users(id),
            FOREIGN KEY (car_id) REFERENCES cars(id),
            FOREIGN KEY (tour_id) REFERENCES tours(id)
        )
    ");

    return $pdo;
}