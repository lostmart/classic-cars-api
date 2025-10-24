# Paris Classic Car Tours - REST API Specification

## Project Overview

### Business Context

A Paris-based company offering guided tours in classic cars. Each tour provides customers with a unique experience combining:

- Specific scenic routes through Paris
- Vintage classic cars with historical significance
- Dedicated professional drivers assigned to each vehicle

### Purpose

This REST API manages the complete booking and operational system for the classic car tour company, handling customers, drivers, vehicles, tour routes, and reservations.

---

## Technical Stack

### Backend

- **Language**: PHP (vanilla, with potential migration to Slim Framework)
- **Database**: SQLite
- **Hosting**: Railway (automatic deployment from GitHub)

### Development & Quality

- **Version Control**: GitHub
- **CI/CD**: GitHub Actions
- **Code Quality**: SonarCloud (reporting mode, no build failure)
- **Testing**: PHPUnit (to be implemented)
- **Image Storage**: Picsum Photos (placeholder, to be replaced with permanent solution)

### Deployment Strategy

- Automatic deployment via Railway on every push to main branch
- Database migrations handled via PHP script on application startup
- Database file excluded from version control for security

---

## User Roles

### 1. Customer

Regular users who book and pay for tours.

**Permissions**:

- Browse available tours
- View classic car details
- Create bookings
- View their own booking history
- Update their profile

### 2. Driver (Employee)

Company employees assigned to specific classic cars.

**Permissions**:

- View their assigned car details
- View their tour schedule/bookings
- Update tour status (completed, in-progress, etc.)
- View customer information for their tours

### 3. Administrator

System administrators managing the entire platform.

**Permissions**:

- Full CRUD operations on all entities
- Manage users (customers, drivers, admins)
- Assign drivers to cars
- Create and manage tours
- View all bookings and analytics
- System configuration

---

## Data Model

### Core Entities

#### 1. Users

Central authentication table for all user types.

**Fields**:

- `id` (integer, primary key, auto-increment)
- `email` (string, unique, required)
- `password` (string, hashed, required)
- `first_name` (string, required)
- `last_name` (string, required)
- `phone` (string, optional)
- `role` (enum: 'customer', 'driver', 'admin', required)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Relationships**:

- One-to-Many with Bookings (as customer)
- One-to-One with Cars (as driver)

---

#### 2. Cars

Classic vehicles used for tours.

**Fields**:

- `id` (integer, primary key, auto-increment)
- `make` (string, required) - e.g., "Mercedes-Benz"
- `model` (string, required) - e.g., "300SL"
- `year` (integer, required) - e.g., 1955
- `color` (string, required)
- `capacity` (integer, required) - passenger capacity
- `license_plate` (string, unique, required)
- `description` (text, optional) - special features or historical significance
- `image_url` (string, optional) - external image reference
- `driver_id` (integer, foreign key to Users, unique, required)
- `status` (enum: 'available', 'maintenance', 'retired', default: 'available')
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Relationships**:

- One-to-One with Users (driver assignment)
- One-to-Many with Bookings

**Business Rules**:

- Each car must have exactly one assigned driver
- Each driver can be assigned to only one car
- Driver must have role='driver'

---

#### 3. Tours

Predefined routes and experiences offered by the company.

**Fields**:

- `id` (integer, primary key, auto-increment)
- `name` (string, required) - e.g., "Romantic Seine Tour"
- `description` (text, required) - tour highlights and route description
- `duration_minutes` (integer, required) - tour length
- `price` (decimal, required) - base price in euros
- `max_passengers` (integer, required) - maximum group size
- `route_highlights` (text, optional) - key landmarks/stops
- `status` (enum: 'active', 'inactive', default: 'active')
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Relationships**:

- One-to-Many with Bookings

**Business Rules**:

- Tours are independent of specific cars (any available car can be used)
- Price is base price; final price depends on passenger count

---

#### 4. Bookings

Customer reservations linking users, tours, and cars.

**Fields**:

- `id` (integer, primary key, auto-increment)
- `customer_id` (integer, foreign key to Users, required)
- `tour_id` (integer, foreign key to Tours, required)
- `car_id` (integer, foreign key to Cars, required)
- `booking_date` (date, required) - date of the tour
- `booking_time` (time, required) - start time of the tour
- `passenger_count` (integer, required) - number of passengers
- `total_price` (decimal, required) - calculated final price
- `status` (enum: 'pending', 'confirmed', 'completed', 'cancelled', default: 'pending')
- `special_requests` (text, optional) - customer notes
- `created_at` (timestamp) - when booking was made
- `updated_at` (timestamp)

**Relationships**:

- Many-to-One with Users (customer)
- Many-to-One with Tours
- Many-to-One with Cars

**Business Rules**:

- Customer must have role='customer'
- Passenger count cannot exceed car capacity
- Passenger count cannot exceed tour max_passengers
- No double-booking: one car cannot have overlapping bookings
- Booking date must be in the future
- Car must be 'available' status
- Tour must be 'active' status
- Driver is implicitly assigned through car relationship

---

## Entity Relationships Diagram (Text Format)

```
Users (role: customer)
  |
  | 1:N
  |
  v
Bookings
  |
  | N:1        N:1
  |------------+------------+
  v                         v
Tours                     Cars
                            ^
                            | 1:1
                            |
                      Users (role: driver)
```

---

## Core Functionalities

### Authentication & Authorization

- User registration (customers only via public endpoint)
- User login (all roles)
- JWT or session-based authentication
- Role-based access control for endpoints

### Customer Features

- Browse available tours
- View tour details with pricing
- Search/filter tours by duration, price
- View available cars (with images)
- Create booking (select tour + date/time)
- View personal booking history
- Cancel bookings (with time restrictions)
- Update profile information

### Driver Features

- View assigned car details
- View upcoming tour schedule
- View booking details for their tours
- Mark tours as completed
- View customer contact information for upcoming tours

### Administrator Features

- Full user management (CRUD)
- Car management (CRUD)
- Driver-to-car assignment
- Tour management (CRUD)
- View all bookings with filtering
- Booking management (confirm, cancel)
- System statistics and reports
- Change booking status

---

## API Design Principles

### RESTful Structure

- Resource-based URLs
- HTTP methods: GET, POST, PUT, DELETE
- Proper status codes
- JSON request/response format

### Endpoint Naming Convention

```
/api/v1/resource
/api/v1/resource/{id}
/api/v1/resource/{id}/subresource
```

### Response Format

```json
{
  "success": true/false,
  "data": {...} or [...],
  "message": "descriptive message",
  "errors": [...]  // if applicable
}
```

---

## High-Level Endpoint Structure

### Public Endpoints

- `POST /api/v1/auth/register` - Customer registration
- `POST /api/v1/auth/login` - User login
- `GET /api/v1/tours` - List all active tours
- `GET /api/v1/tours/{id}` - Tour details
- `GET /api/v1/cars` - List available cars

### Customer Endpoints (Authenticated)

- `GET /api/v1/bookings` - User's bookings
- `POST /api/v1/bookings` - Create booking
- `GET /api/v1/bookings/{id}` - Booking details
- `PUT /api/v1/bookings/{id}` - Update booking
- `DELETE /api/v1/bookings/{id}` - Cancel booking
- `GET /api/v1/profile` - User profile
- `PUT /api/v1/profile` - Update profile

### Driver Endpoints (Authenticated)

- `GET /api/v1/driver/schedule` - Upcoming tours
- `GET /api/v1/driver/car` - Assigned car details
- `PUT /api/v1/driver/bookings/{id}` - Update booking status

### Admin Endpoints (Authenticated)

- Full CRUD for: `/api/v1/admin/users`, `/api/v1/admin/cars`, `/api/v1/admin/tours`, `/api/v1/admin/bookings`
- `POST /api/v1/admin/cars/{id}/assign-driver` - Assign driver to car
- `GET /api/v1/admin/statistics` - System analytics

---

## Validation Rules

### Bookings

- Booking date must be future date
- Passenger count: 1 to min(car.capacity, tour.max_passengers)
- No overlapping bookings for same car
- Car must be available status
- Tour must be active status

### Cars

- Year must be between 1900 and current year
- Capacity must be positive integer (1-8 typical)
- Driver must exist and have driver role
- Driver can only be assigned to one car

### Tours

- Duration must be positive integer
- Price must be positive decimal
- Max passengers must be positive integer

### Users

- Email must be valid and unique
- Password minimum 8 characters
- Phone number format validation (optional)

---

## Database Initialization

### Startup Script

A PHP script runs on every application start that:

1. Checks if database file exists
2. Creates tables if they don't exist
3. Seeds dummy data only if tables are empty

### Seed Data

**Dummy Users** (5-10 of each type):

- Customers with French names
- Drivers (employees)
- At least 1 admin

**Classic Cars** (5-8 vehicles):

- Mix of famous classic cars (Mercedes 300SL, Citroën DS, Jaguar E-Type, etc.)
- Each assigned to a driver
- Picsum placeholder images

**Tours** (4-6 routes):

- Various Paris routes (Seine tour, Montmartre tour, Champs-Élysées, etc.)
- Different durations (30min, 1hr, 2hrs)
- Different price points

**Sample Bookings**:

- Mix of past and future bookings
- Various statuses

---

## Security Considerations

### Data Protection

- Passwords hashed (bcrypt or similar)
- Database file not in version control
- Environment variables for sensitive config
- Input validation and sanitization

### API Security

- Authentication required for protected endpoints
- Role-based authorization checks
- Rate limiting (to be implemented)
- CORS configuration for frontend access

### SQL Injection Prevention

- Prepared statements/parameterized queries
- Input validation before database operations

---

## CI/CD Pipeline

### GitHub Actions Workflow

**Triggers**: Push to main branch

**Steps**:

1. Checkout code
2. Setup PHP environment
3. Install dependencies (Composer)
4. Run PHP syntax check
5. Run PHPUnit tests (when implemented)
6. SonarCloud code quality analysis (report only, no failure)
7. Deploy to Railway (automatic via Railway integration)

### Quality Gates (Future)

- Code coverage threshold
- Security vulnerability checks
- Code smell detection

---

## Future Enhancements

### Phase 2 Features

- Email notifications for booking confirmations
- Payment integration (Stripe)
- Real-time availability checking
- Review and rating system
- Photo gallery for each tour
- Multi-language support (EN/FR)

### Technical Improvements

- Migration to Slim Framework for better routing
- Permanent image storage solution (Cloudinary/S3)
- PostgreSQL migration for better concurrency
- Redis caching layer
- Comprehensive test coverage
- API documentation (Swagger/OpenAPI)

### Analytics

- Popular tours dashboard
- Driver performance metrics
- Revenue reports
- Customer retention analysis

---

## Development Guidelines

### Code Style

- Follow PSR-12 PHP coding standards
- Meaningful variable and function names
- Comments for complex business logic
- Consistent error handling

### Git Workflow

- Main branch protected
- Descriptive commit messages
- Push triggers automated testing

### Testing Strategy

- Start with basic endpoint tests
- Gradually add integration tests
- Test authentication and authorization
- Validate business rules

---

## Glossary

- **Classic Car**: Vintage vehicle used for tours, typically 30+ years old
- **Tour**: Predefined route through Paris with fixed duration and pricing
- **Booking**: Customer reservation for a specific tour on a specific date
- **Driver**: Company employee permanently assigned to operate one classic car
- **Customer**: End-user who books and pays for tours
- **Administrator**: System manager with full access to all features

---

## Document Version

- **Version**: 1.0
- **Date**: October 24, 2025
- **Status**: Initial Specification
- **Next Review**: After MVP implementation

---

## Notes

- This specification is a living document and will evolve based on development insights
- Prioritize core booking functionality in MVP
- Keep implementation simple and iterate based on real usage
- Security and data integrity are top priorities
- Continuous integration and deployment
