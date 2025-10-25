<?php

namespace App\Models;

class Tour
{
    private ?int $id;
    private string $name;
    private string $description;
    private int $duration_minutes;
    private float $price;
    
    public function __construct(
        string $name,
        string $description,
        int $duration_minutes,
        float $price,
        ?int $id = null
    ) {
        $this->setName($name);
        $this->setDescription($description);
        $this->setDurationMinutes($duration_minutes);
        $this->setPrice($price);
        $this->id = $id;
    }
    
    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function getDurationMinutes(): int
    {
        return $this->duration_minutes;
    }
    
    public function getPrice(): float
    {
        return $this->price;
    }
    
    // Setters with validation
    public function setName(string $name): void
    {
        if (empty(trim($name))) {
            throw new \InvalidArgumentException('Tour name cannot be empty');
        }
        $this->name = trim($name);
    }
    
    public function setDescription(string $description): void
    {
        if (empty(trim($description))) {
            throw new \InvalidArgumentException('Tour description cannot be empty');
        }
        $this->description = trim($description);
    }
    
    public function setDurationMinutes(int $duration_minutes): void
    {
        if ($duration_minutes <= 0) {
            throw new \InvalidArgumentException('Duration must be a positive integer');
        }
        $this->duration_minutes = $duration_minutes;
    }
    
    public function setPrice(float $price): void
    {
        if ($price <= 0) {
            throw new \InvalidArgumentException('Price must be a positive number');
        }
        $this->price = $price;
    }
    
    // Convert to array for JSON responses
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'price' => $this->price
        ];
    }
    
    // Create from array (useful for database results)
    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['description'],
            (int) $data['duration_minutes'],
            (float) $data['price'],
            isset($data['id']) ? (int) $data['id'] : null
        );
    }
    
    // Validate data before creating model
    public static function validate(array $data): array
    {
        $errors = [];
        
        if (empty($data['name'] ?? '')) {
            $errors[] = 'Name is required';
        }
        
        if (empty($data['description'] ?? '')) {
            $errors[] = 'Description is required';
        }
        
        if (!isset($data['duration_minutes']) || $data['duration_minutes'] <= 0) {
            $errors[] = 'Duration must be a positive integer';
        }
        
        if (!isset($data['price']) || $data['price'] <= 0) {
            $errors[] = 'Price must be a positive number';
        }
        
        return $errors;
    }
}