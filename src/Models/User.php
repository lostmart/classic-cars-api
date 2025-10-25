<?php

namespace App\Models;

class User
{
    private ?int $id;
    private string $external_user_id;
    private string $email;
    private ?string $first_name;
    private ?string $last_name;
    private ?string $phone;
    private string $role;
    private ?string $created_at;
    private ?string $updated_at;
    
    public function __construct(
        string $external_user_id,
        string $email,
        string $role,
        ?string $first_name = null,
        ?string $last_name = null,
        ?string $phone = null,
        ?int $id = null,
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->setExternalUserId($external_user_id);
        $this->setEmail($email);
        $this->setRole($role);
        $this->setFirstName($first_name);
        $this->setLastName($last_name);
        $this->setPhone($phone);
        $this->id = $id;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }
    
    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getExternalUserId(): string
    {
        return $this->external_user_id;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }
    
    public function getLastName(): ?string
    {
        return $this->last_name;
    }
    
    public function getPhone(): ?string
    {
        return $this->phone;
    }
    
    public function getRole(): string
    {
        return $this->role;
    }
    
    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }
    
    public function getUpdatedAt(): ?string
    {
        return $this->updated_at;
    }
    
    // Setters with validation
    public function setExternalUserId(string $external_user_id): void
    {
        if (empty(trim($external_user_id))) {
            throw new \InvalidArgumentException('External user ID cannot be empty');
        }
        $this->external_user_id = trim($external_user_id);
    }
    
    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        $this->email = strtolower(trim($email));
    }
    
    public function setFirstName(?string $first_name): void
    {
        $this->first_name = $first_name ? trim($first_name) : null;
    }
    
    public function setLastName(?string $last_name): void
    {
        $this->last_name = $last_name ? trim($last_name) : null;
    }
    
    public function setPhone(?string $phone): void
    {
        if ($phone && !preg_match('/^\+?[0-9\s\-\(\)]+$/', $phone)) {
            throw new \InvalidArgumentException('Invalid phone number format');
        }
        $this->phone = $phone ? trim($phone) : null;
    }
    
    public function setRole(string $role): void
    {
        $validRoles = ['customer', 'driver', 'admin'];
        if (!in_array($role, $validRoles)) {
            throw new \InvalidArgumentException('Role must be: customer, driver, or admin');
        }
        $this->role = $role;
    }
    
    // Convert to array for JSON responses
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'external_user_id' => $this->external_user_id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'role' => $this->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    // Create from array (useful for database results)
    public static function fromArray(array $data): self
    {
        return new self(
            $data['external_user_id'],
            $data['email'],
            $data['role'],
            $data['first_name'] ?? null,
            $data['last_name'] ?? null,
            $data['phone'] ?? null,
            isset($data['id']) ? (int) $data['id'] : null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }
    
    // Validate data before creating model
    public static function validate(array $data): array
    {
        $errors = [];
        
        if (empty($data['external_user_id'] ?? '')) {
            $errors[] = 'External user ID is required';
        }
        
        if (empty($data['email'] ?? '')) {
            $errors[] = 'Email is required';
        } else {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
        }
        
        if (empty($data['role'] ?? '')) {
            $errors[] = 'Role is required';
        } else {
            $validRoles = ['customer', 'driver', 'admin'];
            if (!in_array($data['role'], $validRoles)) {
                $errors[] = 'Role must be: customer, driver, or admin';
            }
        }
        
        if (isset($data['phone']) && !empty($data['phone'])) {
            if (!preg_match('/^\+?[0-9\s\-\(\)]+$/', $data['phone'])) {
                $errors[] = 'Invalid phone number format';
            }
        }
        
        return $errors;
    }
    
    // Helper methods
    public function getFullName(): ?string
    {
        if ($this->first_name && $this->last_name) {
            return "{$this->first_name} {$this->last_name}";
        }
        return $this->first_name ?? $this->last_name;
    }
    
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }
    
    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}   