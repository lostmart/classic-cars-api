<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

/** @var App $app */

// Health check endpoint
$app->get('/', function (Request $request, Response $response) {
    $data = [
        'success' => true,
        'message' => 'Paris Classic Car Tours API',
        'version' => 'v1',
        'timestamp' => date('c')
    ];
    
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// API v1 group
$app->group('/api/v1', function ($group) {
    
    // Public routes
    $group->get('/health', function (Request $request, Response $response) {
        $data = [
            'success' => true,
            'message' => 'API is running',
            'database' => 'connected'
        ];
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    // Tours routes (public - anyone can view)
    $group->get('/tours', function (Request $request, Response $response) {
        $db = $this->get('db');
        
        $stmt = $db->query("
            SELECT id, name, description, duration_minutes, price, max_passengers, route_highlights, status 
            FROM tours 
            WHERE status = 'active'
            ORDER BY price ASC
        ");
        $tours = $stmt->fetchAll();
        
        $data = [
            'success' => true,
            'data' => $tours,
            'count' => count($tours)
        ];
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    $group->get('/tours/{id}', function (Request $request, Response $response, array $args) {
        $db = $this->get('db');
        $tourId = (int) $args['id'];
        
        $stmt = $db->prepare("
            SELECT id, name, description, duration_minutes, price, max_passengers, route_highlights, status 
            FROM tours 
            WHERE id = ? AND status = 'active'
        ");
        $stmt->execute([$tourId]);
        $tour = $stmt->fetch();
        
        if (!$tour) {
            $data = [
                'success' => false,
                'message' => 'Tour not found'
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        $data = [
            'success' => true,
            'data' => $tour
        ];
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    // Cars routes (public - anyone can view available cars)
    $group->get('/cars', function (Request $request, Response $response) {
        $db = $this->get('db');
        
        $stmt = $db->query("
            SELECT c.id, c.make, c.model, c.year, c.color, c.capacity, c.description, c.image_url,
                   u.first_name as driver_first_name, u.last_name as driver_last_name
            FROM cars c
            JOIN users u ON c.driver_id = u.id
            WHERE c.status = 'available'
            ORDER BY c.year ASC
        ");
        $cars = $stmt->fetchAll();
        
        $data = [
            'success' => true,
            'data' => $cars,
            'count' => count($cars)
        ];
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    $group->get('/cars/{id}', function (Request $request, Response $response, array $args) {
        $db = $this->get('db');
        $carId = (int) $args['id'];
        
        $stmt = $db->prepare("
            SELECT c.id, c.make, c.model, c.year, c.color, c.capacity, c.description, c.image_url,
                   u.first_name as driver_first_name, u.last_name as driver_last_name
            FROM cars c
            JOIN users u ON c.driver_id = u.id
            WHERE c.id = ? AND c.status = 'available'
        ");
        $stmt->execute([$carId]);
        $car = $stmt->fetch();
        
        if (!$car) {
            $data = [
                'success' => false,
                'message' => 'Car not found'
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        $data = [
            'success' => true,
            'data' => $car
        ];
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
    // Authentication routes (to be implemented)
    $group->post('/auth/register', function (Request $request, Response $response) {
        $data = [
            'success' => false,
            'message' => 'Registration endpoint - to be implemented'
        ];
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(501);
    });
    
    $group->post('/auth/login', function (Request $request, Response $response) {
        $data = [
            'success' => false,
            'message' => 'Login endpoint - to be implemented'
        ];
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(501);
    });
    
    // Protected routes (require authentication - to be implemented with middleware)
    $group->group('/bookings', function ($bookingGroup) {
        $bookingGroup->get('', function (Request $request, Response $response) {
            $data = [
                'success' => false,
                'message' => 'Get bookings endpoint - to be implemented with authentication'
            ];
            
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(501);
        });
        
        $bookingGroup->post('', function (Request $request, Response $response) {
            $data = [
                'success' => false,
                'message' => 'Create booking endpoint - to be implemented with authentication'
            ];
            
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(501);
        });
    });
    
    // Admin routes (require admin role - to be implemented with middleware)
    $group->group('/admin', function ($adminGroup) {
        $adminGroup->get('/users', function (Request $request, Response $response) {
            $data = [
                'success' => false,
                'message' => 'Admin users endpoint - to be implemented with role checking'
            ];
            
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(501);
        });
    });
});

// 404 handler
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function (Request $request, Response $response) {
    $data = [
        'success' => false,
        'message' => 'Endpoint not found',
        'path' => $request->getUri()->getPath()
    ];
    
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});