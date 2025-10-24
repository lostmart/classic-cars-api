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
    
    public function insert(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO tours (name, description, duration_minutes, price) 
            VALUES (:name, :description, :duration_minutes, :price)
        ");
        
        return $stmt->execute($data);
    }
    
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM tours");
        return $stmt->fetchAll();
    }
}