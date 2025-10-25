<?php

namespace App\Models;

class Booking
{
    private ?int $id;
    private int $customer_id;
    private int $tour_id;
    private int $car_id;
    private string $booking_date;
    private string $booking_time;
    private int $passenger_count;
    private float $total_price;
    private string $status;
    private ?string $special_requests;
    private ?string $created_at;
    private ?string $updated_at;
    
    public function __construct(
        int $customer_id,
        int $tour_id,
        int $car_id,
        string $booking_date,
        string $booking_time,
        int $passenger_count,
        float $total_price,
        string $status = 'pending',
        ?string $special_requests = null,
        ?int $id = null,
        ?string $created_at = null,
        ?string $updated_at = null
    ) {
        $this->setCustomerId($customer_id);
        $this->setTourId($tour_id);
        $this->setCarId($car_id);
        $this->setBookingDate($booking_date);
        $this->setBookingTime($booking_time);
        $this->setPassengerCount($passenger_count);
        $this->setTotalPrice($total_price);
        $this->setStatus($status);
        $this->setSpecialRequests($special_requests);
        $this->id = $id;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }
    
    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getCustomerId(): int
    {
        return $this->customer_id;
    }
    
    public function getTourId(): int
    {
        return $this->tour_id;
    }
    
    public function getCarId(): int
    {
        return $this->car_id;
    }
    
    public function getBookingDate(): string
    {
        return $this->booking_date;
    }
    
    public function getBookingTime(): string
    {
        return $this->booking_time;
    }
    
    public function getPassengerCount(): int
    {
        return $this->passenger_count;
    }
    
    public function getTotalPrice(): float
    {
        return $this->total_price;
    }
    
    public function getStatus(): string
    {
        return $this->status;
    }
    
    public function getSpecialRequests(): ?string
    {
        return $this->special_requests;
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
    public function setCustomerId(int $customer_id): void
    {
        if ($customer_id <= 0) {
            throw new \InvalidArgumentException('Customer ID must be positive');
        }
        $this->customer_id = $customer_id;
    }
    
    public function setTourId(int $tour_id): void
    {
        if ($tour_id <= 0) {
            throw new \InvalidArgumentException('Tour ID must be positive');
        }
        $this->tour_id = $tour_id;
    }
    
    public function setCarId(int $car_id): void
    {
        if ($car_id <= 0) {
            throw new \InvalidArgumentException('Car ID must be positive');
        }
        $this->car_id = $car_id;
    }
    
    public function setBookingDate(string $booking_date): void
    {
        // Validate date format (YYYY-MM-DD)
        $date = \DateTime::createFromFormat('Y-m-d', $booking_date);
        if (!$date || $date->format('Y-m-d') !== $booking_date) {
            throw new \InvalidArgumentException('Invalid date format. Use YYYY-MM-DD');
        }
        
        // Check if date is in the future
        $today = new \DateTime('today');
        if ($date < $today) {
            throw new \InvalidArgumentException('Booking date must be in the future');
        }
        
        $this->booking_date = $booking_date;
    }
    
    public function setBookingTime(string $booking_time): void
    {
        // Validate time format (HH:MM or HH:MM:SS)
        if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $booking_time)) {
            throw new \InvalidArgumentException('Invalid time format. Use HH:MM or HH:MM:SS');
        }
        $this->booking_time = $booking_time;
    }
    
    public function setPassengerCount(int $passenger_count): void
    {
        if ($passenger_count < 1) {
            throw new \InvalidArgumentException('Passenger count must be at least 1');
        }
        if ($passenger_count > 8) {
            throw new \InvalidArgumentException('Passenger count cannot exceed 8');
        }
        $this->passenger_count = $passenger_count;
    }
    
    public function setTotalPrice(float $total_price): void
    {
        if ($total_price <= 0) {
            throw new \InvalidArgumentException('Total price must be positive');
        }
        $this->total_price = $total_price;
    }
    
    public function setStatus(string $status): void
    {
        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Status must be: pending, confirmed, completed, or cancelled');
        }
        $this->status = $status;
    }
    
    public function setSpecialRequests(?string $special_requests): void
    {
        $this->special_requests = $special_requests ? trim($special_requests) : null;
    }
    
    // Convert to array for JSON responses
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'tour_id' => $this->tour_id,
            'car_id' => $this->car_id,
            'booking_date' => $this->booking_date,
            'booking_time' => $this->booking_time,
            'passenger_count' => $this->passenger_count,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'special_requests' => $this->special_requests,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    // Create from array (useful for database results)
    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['customer_id'],
            (int) $data['tour_id'],
            (int) $data['car_id'],
            $data['booking_date'],
            $data['booking_time'],
            (int) $data['passenger_count'],
            (float) $data['total_price'],
            $data['status'] ?? 'pending',
            $data['special_requests'] ?? null,
            isset($data['id']) ? (int) $data['id'] : null,
            $data['created_at'] ?? null,
            $data['updated_at'] ?? null
        );
    }
    
    // Validate data before creating model
    public static function validate(array $data): array
    {
        $errors = [];
        
        if (!isset($data['customer_id']) || $data['customer_id'] <= 0) {
            $errors[] = 'Valid customer ID is required';
        }
        
        if (!isset($data['tour_id']) || $data['tour_id'] <= 0) {
            $errors[] = 'Valid tour ID is required';
        }
        
        if (!isset($data['car_id']) || $data['car_id'] <= 0) {
            $errors[] = 'Valid car ID is required';
        }
        
        if (empty($data['booking_date'] ?? '')) {
            $errors[] = 'Booking date is required';
        } else {
            $date = \DateTime::createFromFormat('Y-m-d', $data['booking_date']);
            if (!$date || $date->format('Y-m-d') !== $data['booking_date']) {
                $errors[] = 'Invalid date format. Use YYYY-MM-DD';
            } else {
                $today = new \DateTime('today');
                if ($date < $today) {
                    $errors[] = 'Booking date must be in the future';
                }
            }
        }
        
        if (empty($data['booking_time'] ?? '')) {
            $errors[] = 'Booking time is required';
        } else {
            if (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $data['booking_time'])) {
                $errors[] = 'Invalid time format. Use HH:MM or HH:MM:SS';
            }
        }
        
        if (!isset($data['passenger_count'])) {
            $errors[] = 'Passenger count is required';
        } else {
            $count = (int) $data['passenger_count'];
            if ($count < 1) {
                $errors[] = 'Passenger count must be at least 1';
            }
            if ($count > 8) {
                $errors[] = 'Passenger count cannot exceed 8';
            }
        }
        
        if (!isset($data['total_price']) || $data['total_price'] <= 0) {
            $errors[] = 'Valid total price is required';
        }
        
        if (isset($data['status'])) {
            $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
            if (!in_array($data['status'], $validStatuses)) {
                $errors[] = 'Status must be: pending, confirmed, completed, or cancelled';
            }
        }
        
        return $errors;
    }
    
    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
    
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    
    public function canBeCancelled(): bool
    {
        // Can only cancel pending or confirmed bookings
        return in_array($this->status, ['pending', 'confirmed']);
    }
    
    public function getFullDateTime(): string
    {
        return "{$this->booking_date} {$this->booking_time}";
    }
}