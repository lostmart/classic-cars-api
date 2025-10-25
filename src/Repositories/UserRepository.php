<?php

namespace App\Repositories;

use PDO;

class UserRepository
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = $GLOBALS['db'];
    }
    
    private function ensureTableExists(): void
    {
        try {
            $this->db->query("SELECT 1 FROM users LIMIT 1");
        } catch (\PDOException $e) {
            $this->createTable();
        }
    }
    
    public function createTable(): bool
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                external_user_id TEXT UNIQUE NOT NULL,
                email TEXT NOT NULL,
                first_name TEXT,
                last_name TEXT,
                phone TEXT,
                role TEXT NOT NULL CHECK(role IN ('customer', 'driver', 'admin')),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        return true;
    }
    
    public function insert(array $data): int
    {
        $this->ensureTableExists();
        
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (external_user_id, email, first_name, last_name, phone, role) 
            VALUES (:external_user_id, :email, :first_name, :last_name, :phone, :role)
        ");
        
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function findAll(): array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    public function findById(int $id): ?array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }
    
    public function findByExternalId(string $external_user_id): ?array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE external_user_id = :external_user_id");
        $stmt->execute(['external_user_id' => $external_user_id]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }
    
    public function findByEmail(string $email): ?array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => strtolower($email)]);
        $user = $stmt->fetch();
        
        return $user ?: null;
    }
    
    public function findByRole(string $role): array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE role = :role 
            ORDER BY created_at DESC
        ");
        $stmt->execute(['role' => $role]);
        
        return $stmt->fetchAll();
    }
    
    public function update(int $id, array $data): bool
    {
        $this->ensureTableExists();
        
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        unset($data['external_user_id']); // Can't change external ID
        
        $data['id'] = $id;
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET email = :email,
                first_name = :first_name,
                last_name = :last_name,
                phone = :phone,
                role = :role,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        return $stmt->execute($data);
    }
    
    public function updateRole(int $id, string $role): bool
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET role = :role, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        
        return $stmt->execute(['id' => $id, 'role' => $role]);
    }
    
    public function delete(int $id): bool
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    // Business logic queries
    public function existsByEmail(string $email): bool
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE email = :email");
        $stmt->execute(['email' => strtolower($email)]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    public function existsByExternalId(string $external_user_id): bool
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE external_user_id = :external_user_id");
        $stmt->execute(['external_user_id' => $external_user_id]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    public function countByRole(string $role): int
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE role = :role");
        $stmt->execute(['role' => $role]);
        $result = $stmt->fetch();
        
        return (int) $result['count'];
    }
    
    public function getAllCustomers(): array
    {
        return $this->findByRole('customer');
    }
    
    public function getAllDrivers(): array
    {
        return $this->findByRole('driver');
    }
    
    public function getAllAdmins(): array
    {
        return $this->findByRole('admin');
    }
    
    public function getAvailableDrivers(): array
    {
        $this->ensureTableExists();
        
        // Drivers who don't have a car assigned
        $stmt = $this->db->query("
            SELECT u.* FROM users u
            LEFT JOIN cars c ON u.id = c.driver_id
            WHERE u.role = 'driver' AND c.id IS NULL
            ORDER BY u.created_at DESC
        ");
        
        return $stmt->fetchAll();
    }
    
    // Sync user from external auth service
    public function syncFromAuthService(array $userData): int
    {
        $this->ensureTableExists();
        
        // Check if user already exists by external_user_id
        $existingUser = $this->findByExternalId($userData['external_user_id']);
        
        if ($existingUser) {
            // Update existing user
            $this->update($existingUser['id'], $userData);
            return $existingUser['id'];
        } else {
            // Create new user
            return $this->insert($userData);
        }
    }
}