<?php

namespace App\Models;

class Car
{
    private ?int $id;
    private string $make;
    private string $model;
    private int $year;
    private string $color;
    private int $capacity;
    private string $license_plate;
    private ?string $description;
    private array $image_urls;
    private int $driver_id;
    private string $status;
    
    public function __construct(
        string $make,
        string $model,
        int $year,
        string $color,
        int $capacity,
        string $license_plate,
        int $driver_id,
        ?string $description = null,
        array $image_urls = [],
        string $status = 'available',
        ?int $id = null
    ) {
        $this->setMake($make);
        $this->setModel($model);
        $this->setYear($year);
        $this->setColor($color);
        $this->setCapacity($capacity);
        $this->setLicensePlate($license_plate);
        $this->setDriverId($driver_id);
        $this->setDescription($description);
        $this->setImageUrls($image_urls);
        $this->setStatus($status);
        $this->id = $id;
    }
    
    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getMake(): string
    {
        return $this->make;
    }
    
    public function getModel(): string
    {
        return $this->model;
    }
    
    public function getYear(): int
    {
        return $this->year;
    }
    
    public function getColor(): string
    {
        return $this->color;
    }
    
    public function getCapacity(): int
    {
        return $this->capacity;
    }
    
    public function getLicensePlate(): string
    {
        return $this->license_plate;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function getImageUrl(): ?string
    {
        return $this->image_urls[0] ?? null;
    }
    
    public function getDriverId(): int
    {
        return $this->driver_id;
    }
    
    public function getStatus(): string
    {
        return $this->status;
    }
    
    // Setters with validation
    public function setMake(string $make): void
    {
        if (empty(trim($make))) {
            throw new \InvalidArgumentException('Car make cannot be empty');
        }
        $this->make = trim($make);
    }
    
    public function setModel(string $model): void
    {
        if (empty(trim($model))) {
            throw new \InvalidArgumentException('Car model cannot be empty');
        }
        $this->model = trim($model);
    }
    
    public function setYear(int $year): void
    {
        $currentYear = (int) date('Y');
        if ($year < 1900 || $year > $currentYear) {
            throw new \InvalidArgumentException("Year must be between 1900 and {$currentYear}");
        }
        $this->year = $year;
    }
    
    public function setColor(string $color): void
    {
        if (empty(trim($color))) {
            throw new \InvalidArgumentException('Car color cannot be empty');
        }
        $this->color = trim($color);
    }
    
    public function setCapacity(int $capacity): void
    {
        if ($capacity < 1 || $capacity > 8) {
            throw new \InvalidArgumentException('Capacity must be between 1 and 8 passengers');
        }
        $this->capacity = $capacity;
    }
    
    public function setLicensePlate(string $license_plate): void
    {
        $cleaned = strtoupper(trim($license_plate));
        if (empty($cleaned)) {
            throw new \InvalidArgumentException('License plate cannot be empty');
        }
        // Basic format validation (can be customized for French plates)
        if (!preg_match('/^[A-Z0-9\-]{5,10}$/', $cleaned)) {
            throw new \InvalidArgumentException('Invalid license plate format');
        }
        $this->license_plate = $cleaned;
    }
    
    public function setDescription(?string $description): void
    {
        $this->description = $description ? trim($description) : null;
    }
    
   public function setImageUrls(array $image_urls): void
    {
        $validUrls = [];
        
        foreach ($image_urls as $url) {
            if (empty($url)) {
                continue; // Skip empty values
            }
            
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \InvalidArgumentException("Invalid image URL format: {$url}");
            }
            
            $validUrls[] = $url;
        }
        
        $this->image_urls = $validUrls;
    }
    
    public function setDriverId(int $driver_id): void
    {
        if ($driver_id <= 0) {
            throw new \InvalidArgumentException('Driver ID must be positive');
        }
        $this->driver_id = $driver_id;
    }
    
    public function setStatus(string $status): void
    {
        $validStatuses = ['available', 'maintenance', 'retired'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Status must be: available, maintenance, or retired');
        }
        $this->status = $status;
    }
    
    // Convert to array for JSON responses
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'capacity' => $this->capacity,
            'license_plate' => $this->license_plate,
            'description' => $this->description,
            'image_urls' => $this->image_urls,
            'driver_id' => $this->driver_id,
            'status' => $this->status
        ];
    }
    
    // Create from array (useful for database results)
    public static function fromArray(array $data): self
    {

        $imageUrls = [];
        if (isset($data['image_urls'])) {
            $imageUrls = is_array($data['image_urls']) 
                ? $data['image_urls'] 
                : json_decode($data['image_urls'], true) ?? [];
        } elseif (isset($data['image_url'])) {
            // Backward compatibility: convert old single URL to array
            $imageUrls = [$data['image_url']];
        }

        return new self(
            $data['make'],
            $data['model'],
            (int) $data['year'],
            $data['color'],
            (int) $data['capacity'],
            $data['license_plate'],
            (int) $data['driver_id'],
            $data['description'] ?? null,
            $imageUrls,
            $data['status'] ?? 'available',
            isset($data['id']) ? (int) $data['id'] : null
        );
    }
    
    // Validate data before creating model
    public static function validate(array $data): array
    {
        $errors = [];
        
        if (empty($data['make'] ?? '')) {
            $errors[] = 'Make is required';
        }
        
        if (empty($data['model'] ?? '')) {
            $errors[] = 'Model is required';
        }
        
        if (!isset($data['year'])) {
            $errors[] = 'Year is required';
        } else {
            $currentYear = (int) date('Y');
            $year = (int) $data['year'];
            if ($year < 1900 || $year > $currentYear) {
                $errors[] = "Year must be between 1900 and {$currentYear}";
            }
        }
        
        if (empty($data['color'] ?? '')) {
            $errors[] = 'Color is required';
        }
        
        if (!isset($data['capacity'])) {
            $errors[] = 'Capacity is required';
        } else {
            $capacity = (int) $data['capacity'];
            if ($capacity < 1 || $capacity > 8) {
                $errors[] = 'Capacity must be between 1 and 8 passengers';
            }
        }
        
        if (empty($data['license_plate'] ?? '')) {
            $errors[] = 'License plate is required';
        } else {
            $plate = strtoupper(trim($data['license_plate']));
            if (!preg_match('/^[A-Z0-9\-]{5,10}$/', $plate)) {
                $errors[] = 'Invalid license plate format';
            }
        }
        
        if (!isset($data['driver_id']) || $data['driver_id'] <= 0) {
            $errors[] = 'Valid driver ID is required';
        }
        
        // CHANGED: Validate array of image URLs
        if (isset($data['image_urls']) && !empty($data['image_urls'])) {
            if (!is_array($data['image_urls'])) {
                $errors[] = 'Image URLs must be an array';
            } else {
                foreach ($data['image_urls'] as $url) {
                    if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
                        $errors[] = "Invalid image URL format: {$url}";
                    }
                }
            }
        }
        
        if (isset($data['status'])) {
            $validStatuses = ['available', 'maintenance', 'retired'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors[] = 'Status must be: available, maintenance, or retired';
            }
        }
        
        return $errors;
    }
    
    // Helper to get full car name
    public function getFullName(): string
    {
        return "{$this->year} {$this->make} {$this->model}";
    }
    
    // Check if car is bookable
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}