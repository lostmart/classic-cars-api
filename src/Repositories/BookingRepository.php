<?php

namespace App\Repositories;

use PDO;

class BookingRepository
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = $GLOBALS['db'];
    }
    
    private function ensureTableExists(): void
    {
        try {
            $this->db->query("SELECT 1 FROM bookings LIMIT 1");
        } catch (\PDOException $e) {
            $this->createTable();
        }
    }
    
    public function createTable(): bool
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS bookings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                customer_id INTEGER NOT NULL,
                tour_id INTEGER NOT NULL,
                car_id INTEGER NOT NULL,
                booking_date DATE NOT NULL,
                booking_time TIME NOT NULL,
                passenger_count INTEGER NOT NULL,
                total_price REAL NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending', 'confirmed', 'completed', 'cancelled')),
                special_requests TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE RESTRICT,
                FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE RESTRICT
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
            INSERT INTO bookings (customer_id, tour_id, car_id, booking_date, booking_time, passenger_count, total_price, status, special_requests) 
            VALUES (:customer_id, :tour_id, :car_id, :booking_date, :booking_time, :passenger_count, :total_price, :status, :special_requests)
        ");
        
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function findAll(): array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->query("SELECT * FROM bookings ORDER BY booking_date DESC, booking_time DESC");
        return $stmt->fetchAll();
    }
    
    public function findById(int $id): ?array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $booking = $stmt->fetch();
        
        return $booking ?: null;
    }
    
    public function findByCustomerId(int $customer_id): array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("
            SELECT * FROM bookings 
            WHERE customer_id = :customer_id 
            ORDER BY booking_date DESC, booking_time DESC
        ");
        $stmt->execute(['customer_id' => $customer_id]);
        
        return $stmt->fetchAll();
    }
    
    public function findByStatus(string $status): array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("
            SELECT * FROM bookings 
            WHERE status = :status 
            ORDER BY booking_date DESC, booking_time DESC
        ");
        $stmt->execute(['status' => $status]);
        
        return $stmt->fetchAll();
    }
    
    public function findByCarId(int $car_id): array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("
            SELECT * FROM bookings 
            WHERE car_id = :car_id 
            ORDER BY booking_date DESC, booking_time DESC
        ");
        $stmt->execute(['car_id' => $car_id]);
        
        return $stmt->fetchAll();
    }
    
    public function findByDate(string $date): array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("
            SELECT * FROM bookings 
            WHERE booking_date = :date 
            ORDER BY booking_time ASC
        ");
        $stmt->execute(['date' => $date]);
        
        return $stmt->fetchAll();
    }
    
    public function findUpcoming(): array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->query("
            SELECT * FROM bookings 
            WHERE booking_date >= DATE('now') 
            AND status IN ('pending', 'confirmed')
            ORDER BY booking_date ASC, booking_time ASC
        ");
        
        return $stmt->fetchAll();
    }
    
    public function update(int $id, array $data): bool
    {
        $this->ensureTableExists();
        
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        
        $data['id'] = $id;
        
        $stmt = $this->db->prepare("
            UPDATE bookings 
            SET customer_id = :customer_id,
                tour_id = :tour_id,
                car_id = :car_id,
                booking_date = :booking_date,
                booking_time = :booking_time,
                passenger_count = :passenger_count,
                total_price = :total_price,
                status = :status,
                special_requests = :special_requests,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        
        return $stmt->execute($data);
    }
    
    public function updateStatus(int $id, string $status): bool
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("
            UPDATE bookings 
            SET status = :status, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }
    
    public function delete(int $id): bool
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("DELETE FROM bookings WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    // Business logic queries
    public function hasConflictingBooking(int $car_id, string $date, string $time, ?int $exclude_booking_id = null): bool
    {
        $this->ensureTableExists();
        
        // Check if car is already booked at this date/time
        $sql = "
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE car_id = :car_id 
            AND booking_date = :date 
            AND booking_time = :time
            AND status IN ('pending', 'confirmed')
        ";
        
        $params = [
            'car_id' => $car_id,
            'date' => $date,
            'time' => $time
        ];
        
        // Exclude current booking when updating
        if ($exclude_booking_id !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $exclude_booking_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    public function getBookingsByDateRange(string $start_date, string $end_date): array
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("
            SELECT * FROM bookings 
            WHERE booking_date BETWEEN :start_date AND :end_date 
            ORDER BY booking_date ASC, booking_time ASC
        ");
        $stmt->execute([
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
        
        return $stmt->fetchAll();
    }
    
    public function countByStatus(string $status): int
    {
        $this->ensureTableExists();
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = :status");
        $stmt->execute(['status' => $status]);
        $result = $stmt->fetch();
        
        return (int) $result['count'];
    }
    
    public function getTotalRevenue(?string $status = null): float
    {
        $this->ensureTableExists();
        
        if ($status) {
            $stmt = $this->db->prepare("SELECT SUM(total_price) as total FROM bookings WHERE status = :status");
            $stmt->execute(['status' => $status]);
        } else {
            $stmt = $this->db->query("SELECT SUM(total_price) as total FROM bookings");
        }
        
        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }
}