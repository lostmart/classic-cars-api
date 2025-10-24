# Paris Classic Car Tours - REST API

A REST API for managing a Paris-based classic car tour company, built with PHP and Slim Framework.

## Features

- User management (Customers, Drivers, Administrators)
- Classic car fleet management
- Tour route management
- Booking system
- SQLite database with automatic initialization
- RESTful API design

## Requirements

- PHP 8.0 or higher
- Composer
- SQLite3
- Apache/Nginx web server

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

Edit `.env` file and adjust settings as needed.

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

### Protected Endpoints (To be implemented)

- `GET /api/v1/bookings` - Get user bookings
- `POST /api/v1/bookings` - Create new booking
- `GET /api/v1/bookings/{id}` - Get booking details
- `PUT /api/v1/bookings/{id}` - Update booking
- `DELETE /api/v1/bookings/{id}` - Cancel booking

### Admin Endpoints (To be implemented)

- Full CRUD operations on all resources

## Default Credentials

After initialization, you can use these test accounts:

**Admin:**

- Email: `admin@parisclassictours.fr`
- Password: `admin123`

**Driver:**

- Email: `pierre.martin@parisclassictours.fr`
- Password: `driver123`

**Customer:**

- Email: `marie.lefevre@email.fr`
- Password: `customer123`

## Project Structure

```
├── assets/              # Postman collections, documentation
├── bootstrap/           # Application initialization
│   ├── dependencies.php # DI container configuration
│   └── middleware.php   # Global middleware registration
├── config/              # Configuration files
├── database/            # Database files
│   └── init.php         # Database schema and seeding
├── public/              # Web server document root
│   ├── .htaccess        # Apache rewrite rules
│   └── index.php        # Application entry point
├── src/                 # Application source code
│   ├── controllers/     # Request handlers
│   ├── middlewares/     # Custom middleware
│   ├── models/          # Data models/entities
│   ├── repositories/    # Database access layer
│   └── routes/          # Route definitions
├── tests/               # Unit and integration tests
├── .env.example         # Environment variables template
├── .gitignore           # Git ignore rules
├── composer.json        # PHP dependencies
└── README.md            # This file
```

## Testing

```bash
vendor/bin/phpunit
```

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
