# Paris Classic Car Tours - REST API

A REST API for managing a Paris-based classic car tour company, built with PHP and Slim Framework.

## Features

- Tour route management
- Classic car fleet management
- Booking system
- Driver assignment and scheduling
- Role-based authorization (Customer, Driver, Administrator)
- SQLite database with automatic initialization
- RESTful API design
- External authentication integration

## Architecture

This API focuses on business logic and data management. **Authentication is handled by a separate microservice**, allowing for better separation of concerns and scalability.

### Authentication Flow

```
User → Auth Microservice → JWT Token → Tours API (validates token)
```

1. **Authentication Service** (External): Handles user registration, login, password management, and JWT token issuance
2. **Tours API** (This service): Validates JWT tokens, manages user roles, handles business operations

### User Data Strategy

This API maintains a minimal user reference table synced with the authentication service:

- Stores `external_user_id` matching the auth service's user ID
- Keeps business-relevant data (name, role, email)
- **NO passwords or authentication credentials**
- Focuses on authorization (what users can do) not authentication (who they are)

## Requirements

- PHP 8.2 or higher
- Composer
- SQLite3
- Apache/Nginx web server (or PHP built-in server for development)

## Installation

### 1. Clone the repository

```bash
git clone <your-repo-url>
cd paris-classic-tours-api
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

```bash
cp .env.example .env
```

Edit `.env` file and adjust settings as needed:

```env
APP_NAME=Paris Classic Tours API
DB_PATH=database/database.sqlite
APP_DEBUG=true
AUTH_SERVICE_URL=https://your-auth-service.com  # URL of your auth microservice
JWT_SECRET=your-jwt-secret-key  # Shared secret for JWT validation
MY_API_KEY=your-api-key  # API key for additional security
```

### 4. Set up web server

#### Apache

Point your virtual host to the `public/` directory. The `.htaccess` file will handle routing.

Example Apache virtual host:

```apache
<VirtualHost *:80>
    DocumentRoot "/path/to/project/public"
    ServerName api.parisclassictours.local

    <Directory "/path/to/project/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name api.parisclassictours.local;
    root /path/to/project/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Initialize database

The database will be automatically initialized on first run with:

- Table creation
- Seed data (users, cars, tours, sample bookings)

Just access any endpoint and the initialization will trigger.

## Development

### Using PHP built-in server (for development only)

```bash
php -S localhost:8000 -t public
```

Then access: `http://localhost:8000`

## API Endpoints

### Public Endpoints

- `GET /` - API welcome message
- `GET /api/v1/health` - Health check
- `GET /api/v1/tours` - List all active tours
- `GET /api/v1/tours/{id}` - Get tour details
- `GET /api/v1/cars` - List all available cars
- `GET /api/v1/cars/{id}` - Get car details

### Authentication (To be implemented)

- `POST /api/v1/auth/register` - Register new customer
- `POST /api/v1/auth/login` - Login

### Protected Endpoints

- `GET /api/v1/bookings` - Get all bookings
- `POST /api/v1/bookings` - Create new booking
- `GET /api/v1/bookings/{id}` - Get booking details
- `PUT /api/v1/bookings/{id}` - Update booking
- `DELETE /api/v1/bookings/{id}` - Cancel booking
- `GET /api/v1/bookings/upcoming` - Get upcoming bookings
- `GET /api/v1/bookings/customer/{customer_id}` - Get bookings by customer

### Admin Endpoints

- Full CRUD operations on all resources (Users, Cars, Tours, Bookings)
- `GET /api/v1/users/drivers/available` - Find available drivers
- `PATCH /api/v1/cars/{id}/status` - Update car status

## Project Structure

```
├── bootstrap/           # Application initialization
│   └── ...
├── config/              # Configuration files
├── database/            # Database files and seeding
│   ├── database.sqlite  # Default SQLite database
│   └── seed.php         # Database schema and seeding
├── public/              # Web server document root
│   ├── .htaccess        # Apache rewrite rules
│   └── index.php        # Application entry point
├── src/                 # Application source code
│   ├── Controllers/     # Request handlers
│   ├── Middlewares/     # Custom middleware (Auth, CORS, etc.)
│   ├── Models/          # Data models/entities with validation
│   ├── Repositories/    # Database access layer (Repository Pattern)
│   └── routes/          # Route definitions
├── tests/               # Automated tests
│   ├── Unit/            # Unit tests (Models)
│   ├── Integration/     # Integration tests (Repositories)
│   └── TestCase.php     # Base test class
├── .env.example         # Environment variables template
├── composer.json        # PHP dependencies and autoloading
├── phpunit.xml          # PHPUnit configuration
└── README.md            # This file
```

## Testing

The project includes a comprehensive suite of unit and integration tests using PHPUnit.

### Prerequisites for Testing
- PHP SQLite extension enabled (`pdo_sqlite`)

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run with descriptive output
./vendor/bin/phpunit --testdox

# Run specific suites
./vendor/bin/phpunit --testsuite Unit
./vendor/bin/phpunit --testsuite Integration
```

### Test Coverage
- **Unit Tests:** Validate model logic, data integrity, and business rules.
- **Integration Tests:** Verify database interactions and repository patterns using an in-memory SQLite database.


## Deployment

### Railway

1. Connect your GitHub repository to Railway
2. Set environment variables in Railway dashboard
3. Railway will automatically deploy on push to main

### Manual Deployment

1. Upload files to server (excluding vendor/ and database/)
2. Run `composer install --no-dev --optimize-autoloader`
3. Set proper file permissions
4. Configure web server
5. Set environment variables

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

Private project - All rights reserved

## Support

For issues and questions, please open an issue on GitHub.
