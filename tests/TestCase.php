<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PDO;

/**
 * Base test case class for all tests
 */
abstract class TestCase extends PHPUnitTestCase
{
    protected ?PDO $db = null;

    /**
     * Set up test environment before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->db = createTestDatabase();
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        $this->db = null;
        parent::tearDown();
    }

    /**
     * Create test data helper
     */
    protected function createTestUser(array $data = []): array
    {
        $defaultData = [
            'external_user_id' => 'test_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'role' => 'customer',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+1234567890'
        ];

        $userData = array_merge($defaultData, $data);

        $sql = "INSERT INTO users (external_user_id, email, role, first_name, last_name, phone) 
                VALUES (:external_user_id, :email, :role, :first_name, :last_name, :phone)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($userData);
        
        $userData['id'] = (int) $this->db->lastInsertId();
        return $userData;
    }

    /**
     * Create test car helper
     */
    protected function createTestCar(array $data = []): array
    {
        $defaultData = [
            'make' => 'Test Make',
            'model' => 'Test Model',
            'year' => 2023,
            'color' => 'Blue',
            'capacity' => 4,
            'license_plate' => 'TEST' . rand(1000, 9999),
            'driver_id' => null,
            'status' => 'available',
            'image_urls' => json_encode(['http://example.com/image.jpg'])
        ];

        $carData = array_merge($defaultData, $data);

        $sql = "INSERT INTO cars (make, model, year, color, capacity, license_plate, driver_id, status, image_urls) 
                VALUES (:make, :model, :year, :color, :capacity, :license_plate, :driver_id, :status, :image_urls)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($carData);
        
        $carData['id'] = (int) $this->db->lastInsertId();
        return $carData;
    }

    /**
     * Create test tour helper
     */
    protected function createTestTour(array $data = []): array
    {
        $defaultData = [
            'name' => 'Test Tour',
            'description' => 'Test tour description',
            'duration_hours' => 3,
            'base_price' => 100.00,
            'price_per_person' => 25.00,
            'max_participants' => 8,
            'includes' => json_encode(['Guide', 'Refreshments'])
        ];

        $tourData = array_merge($defaultData, $data);

        $sql = "INSERT INTO tours (name, description, duration_hours, base_price, price_per_person, max_participants, includes) 
                VALUES (:name, :description, :duration_hours, :base_price, :price_per_person, :max_participants, :includes)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($tourData);
        
        $tourData['id'] = (int) $this->db->lastInsertId();
        return $tourData;
    }

    /**
     * Create test booking helper
     */
    protected function createTestBooking(array $data = []): array
    {
        $defaultData = [
            'booking_number' => 'BK' . date('YmdHis') . rand(1000, 9999),
            'customer_id' => 1,
            'car_id' => 1,
            'tour_id' => null,
            'pickup_location' => 'Test Pickup Location',
            'dropoff_location' => 'Test Dropoff Location',
            'booking_date' => date('Y-m-d', strtotime('+1 day')),
            'booking_time' => '10:00:00',
            'duration_hours' => 2,
            'total_passengers' => 2,
            'special_requests' => null,
            'total_price' => 150.00,
            'status' => 'pending',
            'payment_status' => 'pending'
        ];

        $bookingData = array_merge($defaultData, $data);

        $sql = "INSERT INTO bookings (booking_number, customer_id, car_id, tour_id, pickup_location, dropoff_location, 
                booking_date, booking_time, duration_hours, total_passengers, special_requests, total_price, status, payment_status) 
                VALUES (:booking_number, :customer_id, :car_id, :tour_id, :pickup_location, :dropoff_location, 
                :booking_date, :booking_time, :duration_hours, :total_passengers, :special_requests, :total_price, :status, :payment_status)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bookingData);
        
        $bookingData['id'] = (int) $this->db->lastInsertId();
        return $bookingData;
    }

    /**
     * Assert that an array has specific keys
     */
    protected function assertArrayHasKeys(array $keys, array $array, string $message = ''): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array, $message ?: "Array missing key: {$key}");
        }
    }
}