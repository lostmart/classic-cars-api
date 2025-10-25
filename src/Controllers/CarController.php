<?php

namespace App\Controllers;

use App\Repositories\CarRepository;
use App\Models\Car;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CarController
{
    private CarRepository $repository;
    
    public function __construct()
    {
        $this->repository = new CarRepository();
    }
    
    public function create(Request $request, Response $response): Response
    {
        $this->repository->createTable();
        
        $data = [
            'success' => true,
            'message' => 'Cars table created successfully'
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getAll(Request $request, Response $response): Response
    {
        $cars = $this->repository->findAll();
        
        $data = [
            'success' => true,
            'data' => $cars,
            'count' => count($cars)
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getById(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $car = $this->repository->findById($id);
        
        if (!$car) {
            $data = [
                'success' => false,
                'message' => 'Car not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        $data = [
            'success' => true,
            'data' => $car
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function add(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        
        // Validate using Car model
        $errors = Car::validate($body);
        
        if (!empty($errors)) {
            $data = [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        try {
            // Check if license plate already exists
            if ($this->repository->existsByLicensePlate($body['license_plate'])) {
                $data = [
                    'success' => false,
                    'message' => 'License plate already exists'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            
            // Check if driver is already assigned to another car
            if ($this->repository->isDriverAssigned($body['driver_id'])) {
                $data = [
                    'success' => false,
                    'message' => 'Driver is already assigned to another car'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            
            // Create Car model
            $car = Car::fromArray($body);
            
            // Insert into database and get the ID
            $insertedId = $this->repository->insert($car->toArray());
            
            // Create response data with the inserted ID
            $carData = $car->toArray();
            $carData['id'] = $insertedId;
            
            $data = [
                'success' => true,
                'message' => 'Car added successfully',
                'data' => $carData
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            
        } catch (\InvalidArgumentException $e) {
            $data = [
                'success' => false,
                'message' => 'Validation error',
                'errors' => [$e->getMessage()]
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $body = $request->getParsedBody();
        
        // Check if car exists
        $existingCar = $this->repository->findById($id);
        if (!$existingCar) {
            $data = [
                'success' => false,
                'message' => 'Car not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // Validate using Car model
        $errors = Car::validate($body);
        
        if (!empty($errors)) {
            $data = [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        try {
            // Check if license plate is being changed and if new one already exists
            if ($body['license_plate'] !== $existingCar['license_plate']) {
                if ($this->repository->existsByLicensePlate($body['license_plate'])) {
                    $data = [
                        'success' => false,
                        'message' => 'License plate already exists'
                    ];
                    
                    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
                }
            }
            
            // Check if driver is being changed and if new driver is already assigned
            if ($body['driver_id'] !== $existingCar['driver_id']) {
                if ($this->repository->isDriverAssigned($body['driver_id'])) {
                    $data = [
                        'success' => false,
                        'message' => 'Driver is already assigned to another car'
                    ];
                    
                    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
                }
            }
            
            // Create Car model for validation
            $car = Car::fromArray($body);
            
            // Update in database
            $this->repository->update($id, $car->toArray());
            
            // Get updated car
            $updatedCar = $this->repository->findById($id);
            
            $data = [
                'success' => true,
                'message' => 'Car updated successfully',
                'data' => $updatedCar
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\InvalidArgumentException $e) {
            $data = [
                'success' => false,
                'message' => 'Validation error',
                'errors' => [$e->getMessage()]
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Check if car exists
        $car = $this->repository->findById($id);
        if (!$car) {
            $data = [
                'success' => false,
                'message' => 'Car not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // Delete car
        $this->repository->delete($id);
        
        $data = [
            'success' => true,
            'message' => 'Car deleted successfully'
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getByStatus(Request $request, Response $response, array $args): Response
    {
        $status = $args['status'];
        
        // Validate status
        $validStatuses = ['available', 'maintenance', 'retired'];
        if (!in_array($status, $validStatuses)) {
            $data = [
                'success' => false,
                'message' => 'Invalid status. Must be: available, maintenance, or retired'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        $cars = $this->repository->findByStatus($status);
        
        $data = [
            'success' => true,
            'data' => $cars,
            'count' => count($cars),
            'status' => $status
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getByDriverId(Request $request, Response $response, array $args): Response
    {
        $driverId = (int) $args['driver_id'];
        $car = $this->repository->findByDriverId($driverId);
        
        if (!$car) {
            $data = [
                'success' => false,
                'message' => 'No car assigned to this driver'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        $data = [
            'success' => true,
            'data' => $car
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $body = $request->getParsedBody();
        
        // Check if car exists
        $car = $this->repository->findById($id);
        if (!$car) {
            $data = [
                'success' => false,
                'message' => 'Car not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // Validate status
        if (!isset($body['status'])) {
            $data = [
                'success' => false,
                'message' => 'Status is required'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        $validStatuses = ['available', 'maintenance', 'retired'];
        if (!in_array($body['status'], $validStatuses)) {
            $data = [
                'success' => false,
                'message' => 'Invalid status. Must be: available, maintenance, or retired'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Update status
        $this->repository->updateStatus($id, $body['status']);
        
        // Get updated car
        $updatedCar = $this->repository->findById($id);
        
        $data = [
            'success' => true,
            'message' => 'Car status updated successfully',
            'data' => $updatedCar
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
}