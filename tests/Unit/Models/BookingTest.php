<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Booking;
use Tests\TestCase;

class BookingTest extends TestCase
{
    /**
     * Get tomorrow's date in Y-m-d format
     */
    private function getTomorrowDate(): string
    {
        return date('Y-m-d', strtotime('+1 day'));
    }

    /**
     * Test successful booking creation with all data
     */
    public function testCreateBookingWithAllData(): void
    {
        $booking = new Booking(
            1,
            2,
            3,
            $this->getTomorrowDate(),
            '14:00:00',
            4,
            250.00,
            'confirmed',
            'Child seat needed',
            10,
            '2023-01-01 10:00:00',
            '2023-01-02 11:00:00'
        );

        $this->assertEquals(1, $booking->getCustomerId());
        $this->assertEquals(2, $booking->getTourId());
        $this->assertEquals(3, $booking->getCarId());
        $this->assertEquals($this->getTomorrowDate(), $booking->getBookingDate());
        $this->assertEquals('14:00:00', $booking->getBookingTime());
        $this->assertEquals(4, $booking->getPassengerCount());
        $this->assertEquals(250.00, $booking->getTotalPrice());
        $this->assertEquals('confirmed', $booking->getStatus());
        $this->assertEquals('Child seat needed', $booking->getSpecialRequests());
        $this->assertEquals(10, $booking->getId());
        $this->assertEquals('2023-01-01 10:00:00', $booking->getCreatedAt());
        $this->assertEquals('2023-01-02 11:00:00', $booking->getUpdatedAt());
    }

    /**
     * Test booking creation with minimal data
     */
    public function testCreateBookingWithMinimalData(): void
    {
        $booking = new Booking(
            1,
            2,
            3,
            $this->getTomorrowDate(),
            '10:00',
            2,
            150.00
        );

        $this->assertEquals(1, $booking->getCustomerId());
        $this->assertEquals(2, $booking->getTourId());
        $this->assertEquals(3, $booking->getCarId());
        $this->assertEquals($this->getTomorrowDate(), $booking->getBookingDate());
        $this->assertEquals('10:00', $booking->getBookingTime());
        $this->assertEquals(2, $booking->getPassengerCount());
        $this->assertEquals(150.00, $booking->getTotalPrice());
        $this->assertEquals('pending', $booking->getStatus());
        $this->assertNull($booking->getSpecialRequests());
        $this->assertNull($booking->getId());
        $this->assertNull($booking->getCreatedAt());
        $this->assertNull($booking->getUpdatedAt());
    }

    /**
     * Test customer ID validation - zero
     */
    public function testSetCustomerIdZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer ID must be positive');

        new Booking(0, 1, 1, $this->getTomorrowDate(), '10:00', 2, 100.00);
    }

    /**
     * Test customer ID validation - negative
     */
    public function testSetCustomerIdNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer ID must be positive');

        new Booking(-1, 1, 1, $this->getTomorrowDate(), '10:00', 2, 100.00);
    }

    /**
     * Test tour ID validation - zero
     */
    public function testSetTourIdZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tour ID must be positive');

        new Booking(1, 0, 1, $this->getTomorrowDate(), '10:00', 2, 100.00);
    }

    /**
     * Test car ID validation - zero
     */
    public function testSetCarIdZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Car ID must be positive');

        new Booking(1, 1, 0, $this->getTomorrowDate(), '10:00', 2, 100.00);
    }

    /**
     * Test booking date validation - invalid format
     */
    public function testSetBookingDateInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format. Use YYYY-MM-DD');

        new Booking(1, 1, 1, '2023/12/31', '10:00', 2, 100.00);
    }

    /**
     * Test booking date validation - past date
     */
    public function testSetBookingDatePastDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Booking date must be in the future');

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        new Booking(1, 1, 1, $yesterday, '10:00', 2, 100.00);
    }

    /**
     * Test booking date validation - today is valid
     */
    public function testSetBookingDateToday(): void
    {
        $today = date('Y-m-d');
        $booking = new Booking(1, 1, 1, $today, '10:00', 2, 100.00);
        $this->assertEquals($today, $booking->getBookingDate());
    }

    /**
     * Test booking time validation - invalid format
     */
    public function testSetBookingTimeInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid time format. Use HH:MM or HH:MM:SS');

        new Booking(1, 1, 1, $this->getTomorrowDate(), '25:00', 2, 100.00);
    }

    /**
     * Test booking time validation - valid formats
     */
    public function testSetBookingTimeValidFormats(): void
    {
        // Test HH:MM format
        $booking1 = new Booking(1, 1, 1, $this->getTomorrowDate(), '14:30', 2, 100.00);
        $this->assertEquals('14:30', $booking1->getBookingTime());

        // Test HH:MM:SS format
        $booking2 = new Booking(1, 1, 1, $this->getTomorrowDate(), '14:30:45', 2, 100.00);
        $this->assertEquals('14:30:45', $booking2->getBookingTime());

        // Test edge cases
        $booking3 = new Booking(1, 1, 1, $this->getTomorrowDate(), '00:00', 2, 100.00);
        $this->assertEquals('00:00', $booking3->getBookingTime());

        $booking4 = new Booking(1, 1, 1, $this->getTomorrowDate(), '23:59:59', 2, 100.00);
        $this->assertEquals('23:59:59', $booking4->getBookingTime());
    }

    /**
     * Test passenger count validation - zero
     */
    public function testSetPassengerCountZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Passenger count must be at least 1');

        new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 0, 100.00);
    }

    /**
     * Test passenger count validation - exceeds maximum
     */
    public function testSetPassengerCountExceedsMax(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Passenger count cannot exceed 8');

        new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 9, 100.00);
    }

    /**
     * Test passenger count validation - boundary values
     */
    public function testSetPassengerCountBoundaryValues(): void
    {
        $booking1 = new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 1, 100.00);
        $this->assertEquals(1, $booking1->getPassengerCount());

        $booking2 = new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 8, 100.00);
        $this->assertEquals(8, $booking2->getPassengerCount());
    }

    /**
     * Test total price validation - zero
     */
    public function testSetTotalPriceZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Total price must be positive');

        new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 2, 0.00);
    }

    /**
     * Test total price validation - negative
     */
    public function testSetTotalPriceNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Total price must be positive');

        new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 2, -50.00);
    }

    /**
     * Test status validation - invalid status
     */
    public function testSetStatusInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Status must be: pending, confirmed, completed, or cancelled');

        new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 2, 100.00, 'invalid');
    }

    /**
     * Test all valid status values
     */
    public function testSetStatusValidValues(): void
    {
        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        
        foreach ($validStatuses as $status) {
            $booking = new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 2, 100.00, $status);
            $this->assertEquals($status, $booking->getStatus());
        }
    }

    /**
     * Test special requests trimming
     */
    public function testSpecialRequestsTrimming(): void
    {
        $booking = new Booking(
            1, 1, 1,
            $this->getTomorrowDate(),
            '10:00',
            2,
            100.00,
            'pending',
            '  Special request  '
        );
        $this->assertEquals('Special request', $booking->getSpecialRequests());
    }

    /**
     * Test toArray method
     */
    public function testToArray(): void
    {
        $booking = new Booking(
            1, 2, 3,
            $this->getTomorrowDate(),
            '14:00:00',
            4,
            250.00,
            'confirmed',
            'Special request',
            10,
            '2023-01-01',
            '2023-01-02'
        );

        $array = $booking->toArray();

        $this->assertArrayHasKeys([
            'id', 'customer_id', 'tour_id', 'car_id',
            'booking_date', 'booking_time', 'passenger_count',
            'total_price', 'status', 'special_requests',
            'created_at', 'updated_at'
        ], $array);

        $this->assertEquals(10, $array['id']);
        $this->assertEquals(1, $array['customer_id']);
        $this->assertEquals(2, $array['tour_id']);
        $this->assertEquals(3, $array['car_id']);
        $this->assertEquals($this->getTomorrowDate(), $array['booking_date']);
        $this->assertEquals('14:00:00', $array['booking_time']);
        $this->assertEquals(4, $array['passenger_count']);
        $this->assertEquals(250.00, $array['total_price']);
        $this->assertEquals('confirmed', $array['status']);
        $this->assertEquals('Special request', $array['special_requests']);
    }

    /**
     * Test fromArray method
     */
    public function testFromArray(): void
    {
        $data = [
            'id' => 5,
            'customer_id' => 1,
            'tour_id' => 2,
            'car_id' => 3,
            'booking_date' => $this->getTomorrowDate(),
            'booking_time' => '15:30',
            'passenger_count' => 3,
            'total_price' => 199.99,
            'status' => 'confirmed',
            'special_requests' => 'Wheelchair access',
            'created_at' => '2023-01-01',
            'updated_at' => '2023-01-02'
        ];

        $booking = Booking::fromArray($data);

        $this->assertEquals(5, $booking->getId());
        $this->assertEquals(1, $booking->getCustomerId());
        $this->assertEquals(2, $booking->getTourId());
        $this->assertEquals(3, $booking->getCarId());
        $this->assertEquals($this->getTomorrowDate(), $booking->getBookingDate());
        $this->assertEquals('15:30', $booking->getBookingTime());
        $this->assertEquals(3, $booking->getPassengerCount());
        $this->assertEquals(199.99, $booking->getTotalPrice());
        $this->assertEquals('confirmed', $booking->getStatus());
        $this->assertEquals('Wheelchair access', $booking->getSpecialRequests());
    }

    /**
     * Test validate method - all valid
     */
    public function testValidateAllValid(): void
    {
        $data = [
            'customer_id' => 1,
            'tour_id' => 2,
            'car_id' => 3,
            'booking_date' => $this->getTomorrowDate(),
            'booking_time' => '14:00',
            'passenger_count' => 4,
            'total_price' => 200.00,
            'status' => 'pending'
        ];

        $errors = Booking::validate($data);
        $this->assertEmpty($errors);
    }

    /**
     * Test validate method - all invalid
     */
    public function testValidateAllInvalid(): void
    {
        $data = [
            'customer_id' => 0,
            'tour_id' => -1,
            'car_id' => 0,
            'booking_date' => 'invalid',
            'booking_time' => '25:00',
            'passenger_count' => 10,
            'total_price' => 0,
            'status' => 'invalid'
        ];

        $errors = Booking::validate($data);

        $this->assertContains('Valid customer ID is required', $errors);
        $this->assertContains('Valid tour ID is required', $errors);
        $this->assertContains('Valid car ID is required', $errors);
        $this->assertContains('Invalid date format. Use YYYY-MM-DD', $errors);
        $this->assertContains('Invalid time format. Use HH:MM or HH:MM:SS', $errors);
        $this->assertContains('Passenger count cannot exceed 8', $errors);
        $this->assertContains('Valid total price is required', $errors);
        $this->assertContains('Status must be: pending, confirmed, completed, or cancelled', $errors);
    }

    /**
     * Test status helper methods
     */
    public function testStatusHelperMethods(): void
    {
        $pending = new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 2, 100.00, 'pending');
        $this->assertTrue($pending->isPending());
        $this->assertFalse($pending->isConfirmed());
        $this->assertFalse($pending->isCompleted());
        $this->assertFalse($pending->isCancelled());
        $this->assertTrue($pending->canBeCancelled());

        $confirmed = new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 2, 100.00, 'confirmed');
        $this->assertFalse($confirmed->isPending());
        $this->assertTrue($confirmed->isConfirmed());
        $this->assertFalse($confirmed->isCompleted());
        $this->assertFalse($confirmed->isCancelled());
        $this->assertTrue($confirmed->canBeCancelled());

        $completed = new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 2, 100.00, 'completed');
        $this->assertFalse($completed->isPending());
        $this->assertFalse($completed->isConfirmed());
        $this->assertTrue($completed->isCompleted());
        $this->assertFalse($completed->isCancelled());
        $this->assertFalse($completed->canBeCancelled());

        $cancelled = new Booking(1, 1, 1, $this->getTomorrowDate(), '10:00', 2, 100.00, 'cancelled');
        $this->assertFalse($cancelled->isPending());
        $this->assertFalse($cancelled->isConfirmed());
        $this->assertFalse($cancelled->isCompleted());
        $this->assertTrue($cancelled->isCancelled());
        $this->assertFalse($cancelled->canBeCancelled());
    }

    /**
     * Test getFullDateTime method
     */
    public function testGetFullDateTime(): void
    {
        $date = $this->getTomorrowDate();
        $booking = new Booking(1, 1, 1, $date, '14:30:00', 2, 100.00);
        
        $expected = $date . ' 14:30:00';
        $this->assertEquals($expected, $booking->getFullDateTime());
    }
}