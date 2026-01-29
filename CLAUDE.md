# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Paris Classic Car Tours REST API - A PHP Slim Framework 4 API for managing a classic car tour booking system. Authentication is handled by a separate microservice; this API focuses on business logic with JWT token validation.

## Commands

```bash
# Install dependencies
composer install

# Run development server
php -S localhost:8000 -t public

# Run all tests
./vendor/bin/phpunit

# Run tests with descriptive output
./vendor/bin/phpunit --testdox

# Run specific test suites
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration

# Run a single test file
./vendor/bin/phpunit tests/Unit/Models/CarTest.php

# Run a single test method
./vendor/bin/phpunit --filter testMethodName
```

## Architecture

**Layered Architecture Pattern:**

1. **Controllers** (`src/Controllers/`) - Handle HTTP requests, call repositories, return JSON responses
2. **Models** (`src/Models/`) - Domain entities with self-validation in constructors and static `validate()` methods, `toArray()`/`fromArray()` serialization
3. **Repositories** (`src/Repositories/`) - Data access layer with CRUD operations, abstracts SQL from domain
4. **Middlewares** (`src/Middlewares/`) - Cross-cutting concerns (Auth, CORS, JSON parsing)
5. **Exceptions** (`src/Exceptions/`) - Custom exception hierarchy extending `ApiException` with HTTP status mapping

**Key Entry Point:** `public/index.php` - Initializes app, registers error handler, loads routes, auto-seeds database if empty

**Database:** SQLite with auto-initialization via `database/seed.php`. Uses PDO for PostgreSQL migration path.

**Route Organization:** Modular route files in `src/routes/` (tours.php, cars.php, bookings.php, users.php)

## Testing

- **Unit tests** (`tests/Unit/Models/`) - Domain logic and validation
- **Integration tests** (`tests/Integration/Repositories/`) - Database operations with in-memory SQLite
- **Base class:** `tests/TestCase.php`
- **Bootstrap:** `tests/bootstrap.php` - Creates in-memory test database

Tests use `createTestDatabase()` helper for isolated in-memory SQLite instances.

## API Versioning

URL-based versioning: `/api/v1/` prefix for all endpoints.

## Exception Handling

Custom exceptions in `src/Exceptions/`:
- `ApiException` (base) - Includes HTTP status code
- `NotFoundException` (404)
- `ValidationException` (400) - Includes error details array
- `ConflictException` (409)
- `UnauthorizedException` (401)
- `ForbiddenException` (403)

Global handler in `src/ErrorHandler.php` converts exceptions to JSON responses.

## User Roles

Three roles defined in `User` model: `customer`, `driver`, `admin`. User data syncs from external auth service via `external_user_id`.
