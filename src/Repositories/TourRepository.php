<?php

namespace App\Repositories;

use PDO;

class TourRepository
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = $GLOBALS['db'];
    }
    
    public function createTable(): bool
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS tours (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT NOT NULL,
                duration_minutes INTEGER NOT NULL,
                price REAL NOT NULL
            )
        ");
        
        return true;
    }
    
    public function insert(array $data): int
    {
        // Remove id if it exists (it's auto-increment)
        unset($data['id']);
        
        $stmt = $this->db->prepare("
            INSERT INTO tours (name, description, duration_minutes, price) 
            VALUES (:name, :description, :duration_minutes, :price)
        ");
        
        $stmt->execute($data);
        
        // Return the inserted ID
        return (int) $this->db->lastInsertId();
    }
    
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM tours");
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tours WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $tour = $stmt->fetch();
        
        return $tour ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id; // Ensure id is in data for the WHERE clause
        
        $stmt = $this->db->prepare("
            UPDATE tours 
            SET name = :name, description = :description, duration_minutes = :duration_minutes, price = :price 
            WHERE id = :id
        ");
        
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM tours WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}