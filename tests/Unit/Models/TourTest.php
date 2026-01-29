<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Tour;
use Tests\TestCase;

class TourTest extends TestCase
{
    /**
     * Test successful tour creation with valid data
     */
    public function testCreateTourWithValidData(): void
    {
        $tour = new Tour(
            'Countryside Tour',
            'Beautiful scenic tour through the countryside',
            120,
            99.99,
            10
        );

        $this->assertEquals('Countryside Tour', $tour->getName());
        $this->assertEquals('Beautiful scenic tour through the countryside', $tour->getDescription());
        $this->assertEquals(120, $tour->getDurationMinutes());
        $this->assertEquals(99.99, $tour->getPrice());
        $this->assertEquals(10, $tour->getId());
    }

    /**
     * Test tour creation without ID
     */
    public function testCreateTourWithoutId(): void
    {
        $tour = new Tour(
            'City Tour',
            'Explore the city highlights',
            90,
            79.99
        );

        $this->assertEquals('City Tour', $tour->getName());
        $this->assertEquals('Explore the city highlights', $tour->getDescription());
        $this->assertEquals(90, $tour->getDurationMinutes());
        $this->assertEquals(79.99, $tour->getPrice());
        $this->assertNull($tour->getId());
    }

    /**
     * Test name validation - empty name
     */
    public function testSetNameEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tour name cannot be empty');

        new Tour('', 'Description', 60, 50.00);
    }

    /**
     * Test name validation - whitespace only
     */
    public function testSetNameWhitespaceOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tour name cannot be empty');

        new Tour('   ', 'Description', 60, 50.00);
    }

    /**
     * Test name trimming
     */
    public function testNameTrimming(): void
    {
        $tour = new Tour('  Beach Tour  ', 'Description', 60, 50.00);
        $this->assertEquals('Beach Tour', $tour->getName());
    }

    /**
     * Test description validation - empty
     */
    public function testSetDescriptionEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tour description cannot be empty');

        new Tour('Tour Name', '', 60, 50.00);
    }

    /**
     * Test description validation - whitespace only
     */
    public function testSetDescriptionWhitespaceOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tour description cannot be empty');

        new Tour('Tour Name', '   ', 60, 50.00);
    }

    /**
     * Test description trimming
     */
    public function testDescriptionTrimming(): void
    {
        $tour = new Tour('Tour', '  Great tour description  ', 60, 50.00);
        $this->assertEquals('Great tour description', $tour->getDescription());
    }

    /**
     * Test duration validation - zero
     */
    public function testSetDurationZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duration must be a positive integer');

        new Tour('Tour', 'Description', 0, 50.00);
    }

    /**
     * Test duration validation - negative
     */
    public function testSetDurationNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duration must be a positive integer');

        new Tour('Tour', 'Description', -30, 50.00);
    }

    /**
     * Test duration validation - valid values
     */
    public function testSetDurationValid(): void
    {
        $tour1 = new Tour('Tour', 'Description', 1, 50.00);
        $this->assertEquals(1, $tour1->getDurationMinutes());

        $tour2 = new Tour('Tour', 'Description', 480, 50.00);
        $this->assertEquals(480, $tour2->getDurationMinutes());
    }

    /**
     * Test price validation - zero
     */
    public function testSetPriceZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Price must be a positive number');

        new Tour('Tour', 'Description', 60, 0.00);
    }

    /**
     * Test price validation - negative
     */
    public function testSetPriceNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Price must be a positive number');

        new Tour('Tour', 'Description', 60, -10.00);
    }

    /**
     * Test price validation - valid values
     */
    public function testSetPriceValid(): void
    {
        $tour1 = new Tour('Tour', 'Description', 60, 0.01);
        $this->assertEquals(0.01, $tour1->getPrice());

        $tour2 = new Tour('Tour', 'Description', 60, 999999.99);
        $this->assertEquals(999999.99, $tour2->getPrice());
    }

    /**
     * Test toArray method
     */
    public function testToArray(): void
    {
        $tour = new Tour(
            'Wine Tasting',
            'Premium wine tasting experience',
            180,
            149.99,
            5
        );

        $array = $tour->toArray();

        $this->assertArrayHasKeys([
            'id', 'name', 'description', 'duration_minutes', 'price'
        ], $array);

        $this->assertEquals(5, $array['id']);
        $this->assertEquals('Wine Tasting', $array['name']);
        $this->assertEquals('Premium wine tasting experience', $array['description']);
        $this->assertEquals(180, $array['duration_minutes']);
        $this->assertEquals(149.99, $array['price']);
    }

    /**
     * Test fromArray method with complete data
     */
    public function testFromArrayComplete(): void
    {
        $data = [
            'id' => 10,
            'name' => 'Mountain Tour',
            'description' => 'Scenic mountain adventure',
            'duration_minutes' => 240,
            'price' => 199.99
        ];

        $tour = Tour::fromArray($data);

        $this->assertEquals(10, $tour->getId());
        $this->assertEquals('Mountain Tour', $tour->getName());
        $this->assertEquals('Scenic mountain adventure', $tour->getDescription());
        $this->assertEquals(240, $tour->getDurationMinutes());
        $this->assertEquals(199.99, $tour->getPrice());
    }

    /**
     * Test fromArray method without ID
     */
    public function testFromArrayWithoutId(): void
    {
        $data = [
            'name' => 'Lake Tour',
            'description' => 'Beautiful lake views',
            'duration_minutes' => 150,
            'price' => 89.50
        ];

        $tour = Tour::fromArray($data);

        $this->assertNull($tour->getId());
        $this->assertEquals('Lake Tour', $tour->getName());
        $this->assertEquals('Beautiful lake views', $tour->getDescription());
        $this->assertEquals(150, $tour->getDurationMinutes());
        $this->assertEquals(89.50, $tour->getPrice());
    }

    /**
     * Test fromArray with string numbers (from database)
     */
    public function testFromArrayStringNumbers(): void
    {
        $data = [
            'id' => '5',
            'name' => 'Tour',
            'description' => 'Description',
            'duration_minutes' => '120',
            'price' => '99.99'
        ];

        $tour = Tour::fromArray($data);

        $this->assertSame(5, $tour->getId());
        $this->assertSame(120, $tour->getDurationMinutes());
        $this->assertSame(99.99, $tour->getPrice());
    }

    /**
     * Test validate method - all valid
     */
    public function testValidateAllValid(): void
    {
        $data = [
            'name' => 'Valid Tour',
            'description' => 'Valid description',
            'duration_minutes' => 120,
            'price' => 99.99
        ];

        $errors = Tour::validate($data);
        $this->assertEmpty($errors);
    }

    /**
     * Test validate method - all invalid
     */
    public function testValidateAllInvalid(): void
    {
        $data = [
            'name' => '',
            'description' => '',
            'duration_minutes' => 0,
            'price' => 0
        ];

        $errors = Tour::validate($data);

        $this->assertContains('Name is required', $errors);
        $this->assertContains('Description is required', $errors);
        $this->assertContains('Duration must be a positive integer', $errors);
        $this->assertContains('Price must be a positive number', $errors);
    }

    /**
     * Test validate method - missing fields
     */
    public function testValidateMissingFields(): void
    {
        $data = [];

        $errors = Tour::validate($data);

        $this->assertContains('Name is required', $errors);
        $this->assertContains('Description is required', $errors);
        $this->assertContains('Duration must be a positive integer', $errors);
        $this->assertContains('Price must be a positive number', $errors);
    }

    /**
     * Test validate method - negative values
     */
    public function testValidateNegativeValues(): void
    {
        $data = [
            'name' => 'Tour',
            'description' => 'Description',
            'duration_minutes' => -60,
            'price' => -50.00
        ];

        $errors = Tour::validate($data);

        $this->assertContains('Duration must be a positive integer', $errors);
        $this->assertContains('Price must be a positive number', $errors);
    }
}