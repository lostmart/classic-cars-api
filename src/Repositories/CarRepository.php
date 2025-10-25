<?php

namespace App\Repositories;

use PDO;

class CarRepository
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = $GLOBALS['db'];
    }
    
    // ADD THIS NEW METHOD
    private function ensureTableExists(): void
    {
        try {
            // Check if table exists by querying it
            $this->db->query("SELECT 1 FROM cars LIMIT 1");
        } catch (\PDOException $e) {
            // Table doesn't exist, create it
            $this->createTable();
        }
    }
    
    public function createTable(): bool
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS cars (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                make TEXT NOT NULL,
                model TEXT NOT NULL,
                year INTEGER NOT NULL,
                color TEXT NOT NULL,
                capacity INTEGER NOT NULL,
                license_plate TEXT UNIQUE NOT NULL,
                description TEXT,
                image_urls TEXT,
                driver_id INTEGER UNIQUE NOT NULL,
                status TEXT NOT NULL DEFAULT 'available' CHECK(status IN ('available', 'maintenance', 'retired')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE RESTRICT
            )
        ");
        
        return true;
    }
    
    public function insert(array $data): int
    {
        $this->ensureTableExists(); // ADD THIS LINE
        
        // Remove id if it exists (it's auto-increment)
        unset($data['id']);
        
        // Convert image_urls array to JSON string for storage
        if (isset($data['image_urls']) && is_array($data['image_urls'])) {
            $data['image_urls'] = json_encode($data['image_urls']);
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO cars (make, model, year, color, capacity, license_plate, description, image_urls, driver_id, status) 
            VALUES (:make, :model, :year, :color, :capacity, :license_plate, :description, :image_urls, :driver_id, :status)
        ");
        
        $stmt->execute($data);
        
        // Return the inserted ID
        return (int) $this->db->lastInsertId();
    }
    
    public function findAll(): array
    {
        $this->ensureTableExists(); // ADD THIS LINE
        
        $stmt = $this->db->query("SELECT * FROM cars");
        $cars = $stmt->fetchAll();
        
        // Convert JSON image_urls back to array for each car
        return array_map(function($car) {
            $car['image_urls'] = json_decode($car['image_urls'] ?? '[]', true);
            return $car;
        }, $cars);
    }
    
    public function findById(int $id): ?array
    {
        $this->ensureTableExists(); // ADD THIS LINE
        
        $stmt = $this->db->prepare("SELECT * FROM cars WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $car = $stmt->fetch();
        
        if ($car) {
            // Convert JSON image_urls back to array
            $car['image_urls'] = json_decode($car['image_urls'] ?? '[]', true);
            return $car;
        }
        
        return null;
    }
    
    public function findByStatus(string $status): array
    {
        $this->ensureTableExists(); // ADD THIS LINE
        
        $stmt = $this->db->prepare("SELECT * FROM cars WHERE status = :status");
        $stmt->execute(['status' => $status]);
        $cars = $stmt->fetchAll();
        
        // Convert JSON image_urls back to array for each car
        return array_map(function($car) {
            $car['image_urls'] = json_decode($car['image_urls'] ?? '[]', true);
            return $car;
        }, $cars);
    }
    
    public function findByDriverId(int $driver_id): ?array
    {
        $this->ensureTableExists(); // ADD THIS LINE
        
        $stmt = $this->db->prepare("SELECT * FROM cars WHERE driver_id = :driver_id");
        $stmt->execute(['driver_id' => $driver_id]);
        $car = $stmt->fetch();
        
        if ($car) {
            // Convert JSON image_urls back to array
            $car['image_urls'] = json_decode($car['image_urls'] ?? '[]', true);
            return $car;
        }
        
        return null;
    }
    
    public function update(int $id, array $data): bool
    {
        $this->ensureTableExists(); // ADD THIS LINE
        
        // Remove id from data to avoid updating it
        unset($data['id']);
        
        // Convert image_urls array to JSON string for storage
        if (isset($data['image_urls']) && is_array($data['image_urls'])) {
            $data['image_urls'] = json_encode($data['image_urls']);
        }
        
        // Add id for WHERE clause
        $data['id'] = $id;
        
        $stmt = $this->db->prepare("
            UPDATE cars 
            SET make = :make, 
                model = :model, 
                year = :year, 
                color = :color, 
                capacity = :capacity, 
                license_plate = :license_plate, 
                description = :description, 
                image_urls = :image_urls, 
                driver_id = :driver_id, 
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        return $stmt->execute($data);
    }
    
    public function updateStatus(int $id, string $status): bool
    {
        $this->ensureTableExists(); // ADD THIS LINE
        
        $stmt = $this->db->prepare("
            UPDATE cars 
            SET status = :status, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }
    
    public function delete(int $id): bool
    {
        $this->ensureTableExists(); // ADD THIS LINE
        
        $stmt = $this->db->prepare("DELETE FROM cars WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    public function existsByLicensePlate(string $license_plate): bool
    {
        $this->ensureTableExists(); // ADD THIS LINE
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM cars WHERE license_plate = :license_plate");
        $stmt->execute(['license_plate' => $license_plate]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    public function isDriverAssigned(int $driver_id): bool
    {
        $this->ensureTableExists(); // ADD THIS LINE
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM cars WHERE driver_id = :driver_id");
        $stmt->execute(['driver_id' => $driver_id]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}