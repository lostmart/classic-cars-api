<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * Test successful user creation with all data
     */
    public function testCreateUserWithAllData(): void
    {
        $user = new User(
            'ext_123',
            'john.doe@example.com',
            'customer',
            'John',
            'Doe',
            '+1234567890',
            10,
            '2023-01-01 10:00:00',
            '2023-01-02 10:00:00'
        );

        $this->assertEquals('ext_123', $user->getExternalUserId());
        $this->assertEquals('john.doe@example.com', $user->getEmail());
        $this->assertEquals('customer', $user->getRole());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('+1234567890', $user->getPhone());
        $this->assertEquals(10, $user->getId());
        $this->assertEquals('2023-01-01 10:00:00', $user->getCreatedAt());
        $this->assertEquals('2023-01-02 10:00:00', $user->getUpdatedAt());
    }

    /**
     * Test user creation with minimal required data
     */
    public function testCreateUserWithMinimalData(): void
    {
        $user = new User(
            'ext_456',
            'jane@example.com',
            'driver'
        );

        $this->assertEquals('ext_456', $user->getExternalUserId());
        $this->assertEquals('jane@example.com', $user->getEmail());
        $this->assertEquals('driver', $user->getRole());
        $this->assertNull($user->getFirstName());
        $this->assertNull($user->getLastName());
        $this->assertNull($user->getPhone());
        $this->assertNull($user->getId());
        $this->assertNull($user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());
    }

    /**
     * Test external user ID validation - empty value
     */
    public function testSetExternalUserIdEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('External user ID cannot be empty');

        new User('', 'email@example.com', 'customer');
    }

    /**
     * Test external user ID validation - whitespace only
     */
    public function testSetExternalUserIdWhitespaceOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('External user ID cannot be empty');

        new User('   ', 'email@example.com', 'customer');
    }

    /**
     * Test email validation - invalid format
     */
    public function testSetEmailInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        new User('ext_123', 'invalid-email', 'customer');
    }

    /**
     * Test email validation - empty
     */
    public function testSetEmailEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        new User('ext_123', '', 'customer');
    }

    /**
     * Test email normalization (lowercase and trim)
     */
    public function testEmailNormalization(): void
    {
        $user = new User('ext_123', '  John.Doe@EXAMPLE.COM  ', 'customer');
        $this->assertEquals('john.doe@example.com', $user->getEmail());
    }

    /**
     * Test various valid email formats
     */
    public function testValidEmailFormats(): void
    {
        $validEmails = [
            'simple@example.com',
            'user+tag@example.com',
            'user.name@example.com',
            'user_name@example.com',
            'user@subdomain.example.com',
            'user@example.co.uk'
        ];

        foreach ($validEmails as $email) {
            $user = new User('ext_' . rand(100, 999), $email, 'customer');
            $this->assertEquals(strtolower($email), $user->getEmail());
        }
    }

    /**
     * Test role validation - invalid role
     */
    public function testSetRoleInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Role must be: customer, driver, or admin');

        new User('ext_123', 'email@example.com', 'invalid_role');
    }

    /**
     * Test all valid role values
     */
    public function testSetRoleValidValues(): void
    {
        $validRoles = ['customer', 'driver', 'admin'];
        
        foreach ($validRoles as $role) {
            $user = new User('ext_' . rand(100, 999), 'test@example.com', $role);
            $this->assertEquals($role, $user->getRole());
        }
    }

    /**
     * Test phone validation - invalid format
     */
    public function testSetPhoneInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phone number format');

        new User('ext_123', 'email@example.com', 'customer', null, null, 'abc123');
    }

    /**
     * Test phone validation - valid formats
     */
    public function testSetPhoneValidFormats(): void
    {
        $validPhones = [
            '+1234567890',
            '1234567890',
            '+1 234 567 890',
            '123-456-7890',
            '(123) 456-7890',
            '+33 1 23 45 67 89'
        ];

        foreach ($validPhones as $phone) {
            $user = new User('ext_' . rand(100, 999), 'test@example.com', 'customer', null, null, $phone);
            $this->assertEquals(trim($phone), $user->getPhone());
        }
    }

    /**
     * Test phone validation - null value
     */
    public function testSetPhoneNull(): void
    {
        $user = new User('ext_123', 'email@example.com', 'customer', null, null, null);
        $this->assertNull($user->getPhone());
    }

    /**
     * Test first name trimming
     */
    public function testFirstNameTrimming(): void
    {
        $user = new User('ext_123', 'email@example.com', 'customer', '  John  ');
        $this->assertEquals('John', $user->getFirstName());
    }

    /**
     * Test last name trimming
     */
    public function testLastNameTrimming(): void
    {
        $user = new User('ext_123', 'email@example.com', 'customer', null, '  Doe  ');
        $this->assertEquals('Doe', $user->getLastName());
    }

    /**
     * Test phone trimming
     */
    public function testPhoneTrimming(): void
    {
        $user = new User('ext_123', 'email@example.com', 'customer', null, null, '  +1234567890  ');
        $this->assertEquals('+1234567890', $user->getPhone());
    }

    /**
     * Test toArray method
     */
    public function testToArray(): void
    {
        $user = new User(
            'ext_123',
            'john@example.com',
            'customer',
            'John',
            'Doe',
            '+1234567890',
            10,
            '2023-01-01',
            '2023-01-02'
        );

        $array = $user->toArray();

        $this->assertArrayHasKeys([
            'id', 'external_user_id', 'email', 'first_name', 
            'last_name', 'phone', 'role', 'created_at', 'updated_at'
        ], $array);

        $this->assertEquals(10, $array['id']);
        $this->assertEquals('ext_123', $array['external_user_id']);
        $this->assertEquals('john@example.com', $array['email']);
        $this->assertEquals('John', $array['first_name']);
        $this->assertEquals('Doe', $array['last_name']);
        $this->assertEquals('+1234567890', $array['phone']);
        $this->assertEquals('customer', $array['role']);
        $this->assertEquals('2023-01-01', $array['created_at']);
        $this->assertEquals('2023-01-02', $array['updated_at']);
    }

    /**
     * Test fromArray method with complete data
     */
    public function testFromArrayComplete(): void
    {
        $data = [
            'id' => 10,
            'external_user_id' => 'ext_123',
            'email' => 'john@example.com',
            'role' => 'customer',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+1234567890',
            'created_at' => '2023-01-01',
            'updated_at' => '2023-01-02'
        ];

        $user = User::fromArray($data);

        $this->assertEquals(10, $user->getId());
        $this->assertEquals('ext_123', $user->getExternalUserId());
        $this->assertEquals('john@example.com', $user->getEmail());
        $this->assertEquals('customer', $user->getRole());
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('+1234567890', $user->getPhone());
        $this->assertEquals('2023-01-01', $user->getCreatedAt());
        $this->assertEquals('2023-01-02', $user->getUpdatedAt());
    }

    /**
     * Test fromArray method with minimal data
     */
    public function testFromArrayMinimal(): void
    {
        $data = [
            'external_user_id' => 'ext_456',
            'email' => 'jane@example.com',
            'role' => 'driver'
        ];

        $user = User::fromArray($data);

        $this->assertNull($user->getId());
        $this->assertEquals('ext_456', $user->getExternalUserId());
        $this->assertEquals('jane@example.com', $user->getEmail());
        $this->assertEquals('driver', $user->getRole());
        $this->assertNull($user->getFirstName());
        $this->assertNull($user->getLastName());
        $this->assertNull($user->getPhone());
        $this->assertNull($user->getCreatedAt());
        $this->assertNull($user->getUpdatedAt());
    }

    /**
     * Test validate method - all valid
     */
    public function testValidateAllValid(): void
    {
        $data = [
            'external_user_id' => 'ext_123',
            'email' => 'john@example.com',
            'role' => 'customer',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+1234567890'
        ];

        $errors = User::validate($data);
        $this->assertEmpty($errors);
    }

    /**
     * Test validate method - all invalid
     */
    public function testValidateAllInvalid(): void
    {
        $data = [
            'external_user_id' => '',
            'email' => 'invalid-email',
            'role' => 'invalid',
            'phone' => 'abc123'
        ];

        $errors = User::validate($data);
        
        $this->assertContains('External user ID is required', $errors);
        $this->assertContains('Invalid email format', $errors);
        $this->assertContains('Role must be: customer, driver, or admin', $errors);
        $this->assertContains('Invalid phone number format', $errors);
    }

    /**
     * Test validate method - missing required fields
     */
    public function testValidateMissingFields(): void
    {
        $data = [];
        
        $errors = User::validate($data);
        
        $this->assertContains('External user ID is required', $errors);
        $this->assertContains('Email is required', $errors);
        $this->assertContains('Role is required', $errors);
    }

    /**
     * Test validate method - empty email
     */
    public function testValidateEmptyEmail(): void
    {
        $data = [
            'external_user_id' => 'ext_123',
            'email' => '',
            'role' => 'customer'
        ];

        $errors = User::validate($data);
        $this->assertContains('Email is required', $errors);
    }

    /**
     * Test getFullName method - both names present
     */
    public function testGetFullNameBothNames(): void
    {
        $user = new User('ext_123', 'email@example.com', 'customer', 'John', 'Doe');
        $this->assertEquals('John Doe', $user->getFullName());
    }

    /**
     * Test getFullName method - only first name
     */
    public function testGetFullNameFirstNameOnly(): void
    {
        $user = new User('ext_123', 'email@example.com', 'customer', 'John');
        $this->assertEquals('John', $user->getFullName());
    }

    /**
     * Test getFullName method - only last name
     */
    public function testGetFullNameLastNameOnly(): void
    {
        $user = new User('ext_123', 'email@example.com', 'customer', null, 'Doe');
        $this->assertEquals('Doe', $user->getFullName());
    }

    /**
     * Test getFullName method - no names
     */
    public function testGetFullNameNoNames(): void
    {
        $user = new User('ext_123', 'email@example.com', 'customer');
        $this->assertNull($user->getFullName());
    }

    /**
     * Test isCustomer method
     */
    public function testIsCustomer(): void
    {
        $customer = new User('ext_1', 'customer@example.com', 'customer');
        $driver = new User('ext_2', 'driver@example.com', 'driver');
        $admin = new User('ext_3', 'admin@example.com', 'admin');

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($driver->isCustomer());
        $this->assertFalse($admin->isCustomer());
    }

    /**
     * Test isDriver method
     */
    public function testIsDriver(): void
    {
        $customer = new User('ext_1', 'customer@example.com', 'customer');
        $driver = new User('ext_2', 'driver@example.com', 'driver');
        $admin = new User('ext_3', 'admin@example.com', 'admin');

        $this->assertFalse($customer->isDriver());
        $this->assertTrue($driver->isDriver());
        $this->assertFalse($admin->isDriver());
    }

    /**
     * Test isAdmin method
     */
    public function testIsAdmin(): void
    {
        $customer = new User('ext_1', 'customer@example.com', 'customer');
        $driver = new User('ext_2', 'driver@example.com', 'driver');
        $admin = new User('ext_3', 'admin@example.com', 'admin');

        $this->assertFalse($customer->isAdmin());
        $this->assertFalse($driver->isAdmin());
        $this->assertTrue($admin->isAdmin());
    }

    /**
     * Test string trimming across all text fields
     */
    public function testStringTrimming(): void
    {
        $user = new User(
            '  ext_123  ',
            '  JOHN@EXAMPLE.COM  ',
            'customer',
            '  John  ',
            '  Doe  ',
            '  +1234567890  '
        );

        $this->assertEquals('ext_123', $user->getExternalUserId());
        $this->assertEquals('john@example.com', $user->getEmail()); // Also lowercase
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('+1234567890', $user->getPhone());
    }
}