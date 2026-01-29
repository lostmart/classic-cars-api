<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Car;
use Tests\TestCase;

class CarTest extends TestCase
{
    /**
     * Test successful car creation with valid data
     */
    public function testCreateCarWithValidData(): void
    {
        $car = new Car(
            'Toyota',
            'Camry',
            2023,
            'Blue',
            5,
            'ABC-1234',
            1,
            'Comfortable sedan',
            ['https://example.com/car.jpg'],
            'available',
            10
        );

        $this->assertEquals('Toyota', $car->getMake());
        $this->assertEquals('Camry', $car->getModel());
        $this->assertEquals(2023, $car->getYear());
        $this->assertEquals('Blue', $car->getColor());
        $this->assertEquals(5, $car->getCapacity());
        $this->assertEquals('ABC-1234', $car->getLicensePlate());
        $this->assertEquals(1, $car->getDriverId());
        $this->assertEquals('Comfortable sedan', $car->getDescription());
        $this->assertEquals('https://example.com/car.jpg', $car->getImageUrl());
        $this->assertEquals('available', $car->getStatus());
        $this->assertEquals(10, $car->getId());
    }

    /**
     * Test car creation with minimal required data
     */
    public function testCreateCarWithMinimalData(): void
    {
        $car = new Car(
            'Honda',
            'Civic',
            2022,
            'Red',
            4,
            'XYZ-5678',
            2
        );

        $this->assertEquals('Honda', $car->getMake());
        $this->assertEquals('Civic', $car->getModel());
        $this->assertEquals(2022, $car->getYear());
        $this->assertEquals('Red', $car->getColor());
        $this->assertEquals(4, $car->getCapacity());
        $this->assertEquals('XYZ-5678', $car->getLicensePlate());
        $this->assertEquals(2, $car->getDriverId());
        $this->assertNull($car->getDescription());
        $this->assertNull($car->getImageUrl());
        $this->assertEquals('available', $car->getStatus());
        $this->assertNull($car->getId());
    }

    /**
     * Test make validation - empty make should throw exception
     */
    public function testSetMakeWithEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Car make cannot be empty');

        new Car('', 'Model', 2023, 'Blue', 5, 'ABC-1234', 1);
    }

    /**
     * Test make validation - whitespace only should throw exception
     */
    public function testSetMakeWithWhitespaceOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Car make cannot be empty');

        new Car('   ', 'Model', 2023, 'Blue', 5, 'ABC-1234', 1);
    }

    /**
     * Test model validation - empty model should throw exception
     */
    public function testSetModelWithEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Car model cannot be empty');

        new Car('Toyota', '', 2023, 'Blue', 5, 'ABC-1234', 1);
    }

    /**
     * Test year validation - year before 1900
     */
    public function testSetYearBefore1900(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Year must be between 1900 and');

        new Car('Toyota', 'Camry', 1899, 'Blue', 5, 'ABC-1234', 1);
    }

    /**
     * Test year validation - future year
     */
    public function testSetYearInFuture(): void
    {
        $futureYear = (int) date('Y') + 1;
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Year must be between 1900 and');

        new Car('Toyota', 'Camry', $futureYear, 'Blue', 5, 'ABC-1234', 1);
    }

    /**
     * Test year validation - boundary values
     */
    public function testSetYearBoundaryValues(): void
    {
        $currentYear = (int) date('Y');
        
        // Test minimum boundary
        $car1 = new Car('Toyota', 'Camry', 1900, 'Blue', 5, 'ABC-1234', 1);
        $this->assertEquals(1900, $car1->getYear());
        
        // Test maximum boundary
        $car2 = new Car('Toyota', 'Camry', $currentYear, 'Blue', 5, 'ABC-1234', 1);
        $this->assertEquals($currentYear, $car2->getYear());
    }

    /**
     * Test color validation - empty color
     */
    public function testSetColorWithEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Car color cannot be empty');

        new Car('Toyota', 'Camry', 2023, '', 5, 'ABC-1234', 1);
    }

    /**
     * Test capacity validation - less than 1
     */
    public function testSetCapacityLessThanOne(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Capacity must be between 1 and 8 passengers');

        new Car('Toyota', 'Camry', 2023, 'Blue', 0, 'ABC-1234', 1);
    }

    /**
     * Test capacity validation - more than 8
     */
    public function testSetCapacityMoreThanEight(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Capacity must be between 1 and 8 passengers');

        new Car('Toyota', 'Camry', 2023, 'Blue', 9, 'ABC-1234', 1);
    }

    /**
     * Test capacity validation - boundary values
     */
    public function testSetCapacityBoundaryValues(): void
    {
        // Test minimum boundary
        $car1 = new Car('Toyota', 'Camry', 2023, 'Blue', 1, 'ABC-1234', 1);
        $this->assertEquals(1, $car1->getCapacity());
        
        // Test maximum boundary
        $car2 = new Car('Toyota', 'Camry', 2023, 'Blue', 8, 'XYZ-5678', 1);
        $this->assertEquals(8, $car2->getCapacity());
    }

    /**
     * Test license plate validation - empty
     */
    public function testSetLicensePlateEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('License plate cannot be empty');

        new Car('Toyota', 'Camry', 2023, 'Blue', 5, '', 1);
    }

    /**
     * Test license plate validation - invalid format
     */
    public function testSetLicensePlateInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid license plate format');

        new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'AB@#$', 1);
    }

    /**
     * Test license plate validation - too short
     */
    public function testSetLicensePlateTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid license plate format');

        new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'AB', 1);
    }

    /**
     * Test license plate validation - too long
     */
    public function testSetLicensePlateTooLong(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid license plate format');

        new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABCDEFGHIJK', 1);
    }

    /**
     * Test license plate normalization
     */
    public function testLicensePlateNormalization(): void
    {
        $car = new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'abc-1234', 1);
        $this->assertEquals('ABC-1234', $car->getLicensePlate());
    }

    /**
     * Test driver ID validation - zero
     */
    public function testSetDriverIdZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver ID must be positive');

        new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-1234', 0);
    }

    /**
     * Test driver ID validation - negative
     */
    public function testSetDriverIdNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver ID must be positive');

        new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-1234', -1);
    }

    /**
     * Test status validation - invalid status
     */
    public function testSetStatusInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Status must be: available, maintenance, or retired');

        new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-1234', 1, null, [], 'invalid');
    }

    /**
     * Test all valid status values
     */
    public function testSetStatusValidValues(): void
    {
        $validStatuses = ['available', 'maintenance', 'retired'];
        
        foreach ($validStatuses as $status) {
            $car = new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-' . rand(1000, 9999), 1, null, [], $status);
            $this->assertEquals($status, $car->getStatus());
        }
    }

    /**
     * Test image URLs validation - invalid URL
     */
    public function testSetImageUrlsInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid image URL format');

        new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-1234', 1, null, ['not-a-url']);
    }

    /**
     * Test image URLs validation - mixed valid and empty
     */
    public function testSetImageUrlsMixedValidAndEmpty(): void
    {
        $urls = ['https://example.com/1.jpg', '', 'https://example.com/2.jpg', null];
        $car = new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-1234', 1, null, $urls);
        
        // Should only contain valid URLs
        $this->assertEquals('https://example.com/1.jpg', $car->getImageUrl());
    }

    /**
     * Test image URLs validation - multiple valid URLs
     */
    public function testSetImageUrlsMultipleValid(): void
    {
        $urls = ['https://example.com/1.jpg', 'https://example.com/2.jpg', 'https://example.com/3.jpg'];
        $car = new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-1234', 1, null, $urls);
        
        // getImageUrl returns first URL
        $this->assertEquals('https://example.com/1.jpg', $car->getImageUrl());
    }

    /**
     * Test getImageUrl when no images
     */
    public function testGetImageUrlWhenEmpty(): void
    {
        $car = new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-1234', 1);
        $this->assertNull($car->getImageUrl());
    }

    /**
     * Test description trimming
     */
    public function testDescriptionTrimming(): void
    {
        $car = new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-1234', 1, '  Description with spaces  ');
        $this->assertEquals('Description with spaces', $car->getDescription());
    }

    /**
     * Test toArray method
     */
    public function testToArray(): void
    {
        $car = new Car(
            'Toyota',
            'Camry',
            2023,
            'Blue',
            5,
            'ABC-1234',
            1,
            'Nice car',
            ['https://example.com/car.jpg'],
            'available',
            10
        );

        $array = $car->toArray();

        $this->assertArrayHasKeys([
            'id', 'make', 'model', 'year', 'color', 'capacity',
            'license_plate', 'description', 'image_urls', 'driver_id', 'status'
        ], $array);

        $this->assertEquals(10, $array['id']);
        $this->assertEquals('Toyota', $array['make']);
        $this->assertEquals('Camry', $array['model']);
        $this->assertEquals(2023, $array['year']);
        $this->assertEquals('Blue', $array['color']);
        $this->assertEquals(5, $array['capacity']);
        $this->assertEquals('ABC-1234', $array['license_plate']);
        $this->assertEquals('Nice car', $array['description']);
        $this->assertEquals(['https://example.com/car.jpg'], $array['image_urls']);
        $this->assertEquals(1, $array['driver_id']);
        $this->assertEquals('available', $array['status']);
    }

    /**
     * Test fromArray method with complete data
     */
    public function testFromArrayComplete(): void
    {
        $data = [
            'id' => 10,
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2023,
            'color' => 'Blue',
            'capacity' => 5,
            'license_plate' => 'ABC-1234',
            'driver_id' => 1,
            'description' => 'Nice car',
            'image_urls' => ['https://example.com/car.jpg'],
            'status' => 'available'
        ];

        $car = Car::fromArray($data);

        $this->assertEquals(10, $car->getId());
        $this->assertEquals('Toyota', $car->getMake());
        $this->assertEquals('Camry', $car->getModel());
        $this->assertEquals(2023, $car->getYear());
        $this->assertEquals('Blue', $car->getColor());
        $this->assertEquals(5, $car->getCapacity());
        $this->assertEquals('ABC-1234', $car->getLicensePlate());
        $this->assertEquals(1, $car->getDriverId());
        $this->assertEquals('Nice car', $car->getDescription());
        $this->assertEquals('https://example.com/car.jpg', $car->getImageUrl());
        $this->assertEquals('available', $car->getStatus());
    }

    /**
     * Test fromArray method with JSON image_urls
     */
    public function testFromArrayWithJsonImageUrls(): void
    {
        $data = [
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2023,
            'color' => 'Blue',
            'capacity' => 5,
            'license_plate' => 'ABC-1234',
            'driver_id' => 1,
            'image_urls' => '["https://example.com/1.jpg","https://example.com/2.jpg"]'
        ];

        $car = Car::fromArray($data);
        $this->assertEquals('https://example.com/1.jpg', $car->getImageUrl());
    }

    /**
     * Test fromArray method with legacy image_url field
     */
    public function testFromArrayWithLegacyImageUrl(): void
    {
        $data = [
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2023,
            'color' => 'Blue',
            'capacity' => 5,
            'license_plate' => 'ABC-1234',
            'driver_id' => 1,
            'image_url' => 'https://example.com/legacy.jpg'
        ];

        $car = Car::fromArray($data);
        $this->assertEquals('https://example.com/legacy.jpg', $car->getImageUrl());
    }

    /**
     * Test validate method - all valid
     */
    public function testValidateAllValid(): void
    {
        $data = [
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2023,
            'color' => 'Blue',
            'capacity' => 5,
            'license_plate' => 'ABC-1234',
            'driver_id' => 1,
            'status' => 'available'
        ];

        $errors = Car::validate($data);
        $this->assertEmpty($errors);
    }

    /**
     * Test validate method - all invalid
     */
    public function testValidateAllInvalid(): void
    {
        $data = [
            'make' => '',
            'model' => '',
            'year' => 1850,
            'color' => '',
            'capacity' => 10,
            'license_plate' => 'A',
            'driver_id' => 0,
            'status' => 'broken',
            'image_urls' => 'not-an-array'
        ];

        $errors = Car::validate($data);
        
        $this->assertContains('Make is required', $errors);
        $this->assertContains('Model is required', $errors);
        $this->assertContains('Color is required', $errors);
        $this->assertContains('Capacity must be between 1 and 8 passengers', $errors);
        $this->assertContains('Invalid license plate format', $errors);
        $this->assertContains('Valid driver ID is required', $errors);
        $this->assertContains('Status must be: available, maintenance, or retired', $errors);
        $this->assertContains('Image URLs must be an array', $errors);
    }

    /**
     * Test validate method - missing required fields
     */
    public function testValidateMissingFields(): void
    {
        $data = [];
        
        $errors = Car::validate($data);
        
        $this->assertContains('Make is required', $errors);
        $this->assertContains('Model is required', $errors);
        $this->assertContains('Year is required', $errors);
        $this->assertContains('Color is required', $errors);
        $this->assertContains('Capacity is required', $errors);
        $this->assertContains('License plate is required', $errors);
        $this->assertContains('Valid driver ID is required', $errors);
    }

    /**
     * Test getFullName method
     */
    public function testGetFullName(): void
    {
        $car = new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-1234', 1);
        $this->assertEquals('2023 Toyota Camry', $car->getFullName());
    }

    /**
     * Test isAvailable method
     */
    public function testIsAvailable(): void
    {
        // Available car
        $car1 = new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'ABC-1234', 1, null, [], 'available');
        $this->assertTrue($car1->isAvailable());
        
        // Maintenance car
        $car2 = new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'XYZ-5678', 1, null, [], 'maintenance');
        $this->assertFalse($car2->isAvailable());
        
        // Retired car
        $car3 = new Car('Toyota', 'Camry', 2023, 'Blue', 5, 'QWE-9012', 1, null, [], 'retired');
        $this->assertFalse($car3->isAvailable());
    }

    /**
     * Test string trimming across all text fields
     */
    public function testStringTrimming(): void
    {
        $car = new Car(
            '  Toyota  ',
            '  Camry  ',
            2023,
            '  Blue  ',
            5,
            '  abc-1234  ',
            1,
            '  Nice car  '
        );

        $this->assertEquals('Toyota', $car->getMake());
        $this->assertEquals('Camry', $car->getModel());
        $this->assertEquals('Blue', $car->getColor());
        $this->assertEquals('ABC-1234', $car->getLicensePlate()); // Also uppercase
        $this->assertEquals('Nice car', $car->getDescription());
    }
}