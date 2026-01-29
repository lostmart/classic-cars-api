<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use App\Models\Car;
use App\Repositories\CarRepository;
use Tests\TestCase;
use PDO;

class CarRepositoryTest extends TestCase
{
    private CarRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CarRepository($this->db);
        
        // Create test data
        $this->createTestUser(['id' => 1, 'role' => 'driver']);
        $this->createTestUser(['id' => 2, 'role' => 'driver']);
    }

    /**
     * Test creating a new car
     */
    public function testCreateCar(): void
    {
        $carData = [
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2023,
            'color' => 'Blue',
            'capacity' => 5,
            'license_plate' => 'ABC-1234',
            'driver_id' => 1,
            'description' => 'Comfortable sedan',
            'image_urls' => ['https://example.com/car.jpg'],
            'status' => 'available'
        ];

        $car = Car::fromArray($carData);
        $createdCar = $this->repository->create($car);

        $this->assertNotNull($createdCar->getId());
        $this->assertEquals('Toyota', $createdCar->getMake());
        $this->assertEquals('Camry', $createdCar->getModel());
        $this->assertEquals(2023, $createdCar->getYear());
        $this->assertEquals('Blue', $createdCar->getColor());
        $this->assertEquals(5, $createdCar->getCapacity());
        $this->assertEquals('ABC-1234', $createdCar->getLicensePlate());
        $this->assertEquals(1, $createdCar->getDriverId());
        $this->assertEquals('available', $createdCar->getStatus());
    }

    /**
     * Test finding a car by ID
     */
    public function testFindById(): void
    {
        $car = $this->createTestCar(['license_plate' => 'XYZ-5678']);
        
        $foundCar = $this->repository->findById($car['id']);
        
        $this->assertNotNull($foundCar);
        $this->assertEquals($car['id'], $foundCar->getId());
        $this->assertEquals('XYZ-5678', $foundCar->getLicensePlate());
    }

    /**
     * Test finding non-existent car
     */
    public function testFindByIdNotFound(): void
    {
        $car = $this->repository->findById(999);
        $this->assertNull($car);
    }

    /**
     * Test getting all cars
     */
    public function testGetAll(): void
    {
        $this->createTestCar(['license_plate' => 'CAR-001']);
        $this->createTestCar(['license_plate' => 'CAR-002']);
        $this->createTestCar(['license_plate' => 'CAR-003']);
        
        $cars = $this->repository->getAll();
        
        $this->assertCount(3, $cars);
        $this->assertContainsOnlyInstancesOf(Car::class, $cars);
    }

    /**
     * Test getting cars by status
     */
    public function testGetByStatus(): void
    {
        $this->createTestCar(['license_plate' => 'AVAIL-001', 'status' => 'available']);
        $this->createTestCar(['license_plate' => 'AVAIL-002', 'status' => 'available']);
        $this->createTestCar(['license_plate' => 'MAINT-001', 'status' => 'maintenance']);
        $this->createTestCar(['license_plate' => 'RETIRED-001', 'status' => 'retired']);
        
        $availableCars = $this->repository->getByStatus('available');
        $maintenanceCars = $this->repository->getByStatus('maintenance');
        $retiredCars = $this->repository->getByStatus('retired');
        
        $this->assertCount(2, $availableCars);
        $this->assertCount(1, $maintenanceCars);
        $this->assertCount(1, $retiredCars);
    }

    /**
     * Test getting car by driver
     */
    public function testGetByDriver(): void
    {
        $this->createTestCar(['license_plate' => 'DRV1-001', 'driver_id' => 1]);
        $this->createTestCar(['license_plate' => 'DRV1-002', 'driver_id' => 1]);
        $this->createTestCar(['license_plate' => 'DRV2-001', 'driver_id' => 2]);
        
        $driver1Cars = $this->repository->getByDriver(1);
        $driver2Cars = $this->repository->getByDriver(2);
        
        $this->assertCount(2, $driver1Cars);
        $this->assertCount(1, $driver2Cars);
        
        foreach ($driver1Cars as $car) {
            $this->assertEquals(1, $car->getDriverId());
        }
    }

    /**
     * Test updating a car
     */
    public function testUpdate(): void
    {
        $originalData = $this->createTestCar([
            'make' => 'Honda',
            'model' => 'Civic',
            'color' => 'Red',
            'license_plate' => 'UPDATE-001'
        ]);
        
        $car = $this->repository->findById($originalData['id']);
        $car->setColor('Green');
        $car->setStatus('maintenance');
        
        $updated = $this->repository->update($car);
        
        $this->assertTrue($updated);
        
        // Verify the update
        $updatedCar = $this->repository->findById($originalData['id']);
        $this->assertEquals('Green', $updatedCar->getColor());
        $this->assertEquals('maintenance', $updatedCar->getStatus());
        $this->assertEquals('Honda', $updatedCar->getMake()); // Unchanged
    }

    /**
     * Test deleting a car
     */
    public function testDelete(): void
    {
        $car = $this->createTestCar(['license_plate' => 'DELETE-001']);
        
        $deleted = $this->repository->delete($car['id']);
        $this->assertTrue($deleted);
        
        // Verify deletion
        $foundCar = $this->repository->findById($car['id']);
        $this->assertNull($foundCar);
    }

    /**
     * Test checking license plate exists
     */
    public function testLicensePlateExists(): void
    {
        $this->createTestCar(['license_plate' => 'EXIST-001']);
        
        $this->assertTrue($this->repository->licensePlateExists('EXIST-001'));
        $this->assertFalse($this->repository->licensePlateExists('NOTEXIST-001'));
    }

    /**
     * Test checking license plate exists excluding specific car
     */
    public function testLicensePlateExistsExcluding(): void
    {
        $car = $this->createTestCar(['license_plate' => 'UNIQUE-001']);
        
        // Should not exist when excluding the same car
        $this->assertFalse($this->repository->licensePlateExists('UNIQUE-001', $car['id']));
        
        // Should exist when excluding different car
        $this->assertTrue($this->repository->licensePlateExists('UNIQUE-001', 999));
    }

    /**
     * Test checking if driver has cars
     */
    public function testDriverHasCars(): void
    {
        $this->createTestCar(['license_plate' => 'DRVTEST-001', 'driver_id' => 1]);
        
        $this->assertTrue($this->repository->driverHasCars(1));
        $this->assertFalse($this->repository->driverHasCars(2));
    }

    /**
     * Test checking if driver has cars excluding specific car
     */
    public function testDriverHasCarsExcluding(): void
    {
        $car1 = $this->createTestCar(['license_plate' => 'DRVEX-001', 'driver_id' => 1]);
        $this->createTestCar(['license_plate' => 'DRVEX-002', 'driver_id' => 1]);
        
        // Driver 1 has other cars when excluding car1
        $this->assertTrue($this->repository->driverHasCars(1, $car1['id']));
        
        // Driver 2 has no cars
        $this->assertFalse($this->repository->driverHasCars(2));
    }

    /**
     * Test transaction rollback on error
     */
    public function testTransactionRollback(): void
    {
        $initialCount = count($this->repository->getAll());
        
        try {
            $this->db->beginTransaction();
            
            // Create a car
            $car = Car::fromArray([
                'make' => 'Test',
                'model' => 'Transaction',
                'year' => 2023,
                'color' => 'Blue',
                'capacity' => 5,
                'license_plate' => 'TRANS-001',
                'driver_id' => 1
            ]);
            $this->repository->create($car);
            
            // Force an error
            throw new \Exception('Test exception');
            
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
        }
        
        // Verify no car was created
        $finalCount = count($this->repository->getAll());
        $this->assertEquals($initialCount, $finalCount);
        $this->assertFalse($this->repository->licensePlateExists('TRANS-001'));
    }

    /**
     * Test handling special characters in data
     */
    public function testSpecialCharacters(): void
    {
        $car = Car::fromArray([
            'make' => "O'Brien's",
            'model' => 'Model "Special"',
            'year' => 2023,
            'color' => 'Blue & Green',
            'capacity' => 5,
            'license_plate' => 'SPEC-001',
            'driver_id' => 1,
            'description' => "Description with 'quotes' and \"double quotes\""
        ]);
        
        $created = $this->repository->create($car);
        $found = $this->repository->findById($created->getId());
        
        $this->assertEquals("O'Brien's", $found->getMake());
        $this->assertEquals('Model "Special"', $found->getModel());
        $this->assertEquals('Blue & Green', $found->getColor());
        $this->assertEquals("Description with 'quotes' and \"double quotes\"", $found->getDescription());
    }
}