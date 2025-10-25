<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Models\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UserController
{
    private UserRepository $repository;
    
    public function __construct()
    {
        $this->repository = new UserRepository();
    }
    
    public function create(Request $request, Response $response): Response
    {
        $this->repository->createTable();
        
        $data = [
            'success' => true,
            'message' => 'Users table created successfully'
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getAll(Request $request, Response $response): Response
    {
        $users = $this->repository->findAll();
        
        $data = [
            'success' => true,
            'data' => $users,
            'count' => count($users)
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getById(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $user = $this->repository->findById($id);
        
        if (!$user) {
            $data = [
                'success' => false,
                'message' => 'User not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        $data = [
            'success' => true,
            'data' => $user
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function add(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        
        // Check if body is null or empty
        if ($body === null || empty($body)) {
            $data = [
                'success' => false,
                'message' => 'Request body is empty or invalid JSON'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Validate using User model
        $errors = User::validate($body);
        
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
            // Check if email already exists
            if ($this->repository->existsByEmail($body['email'])) {
                $data = [
                    'success' => false,
                    'message' => 'Email already exists'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            
            // Check if external_user_id already exists
            if ($this->repository->existsByExternalId($body['external_user_id'])) {
                $data = [
                    'success' => false,
                    'message' => 'User already synced from auth service'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            
            // Create User model
            $user = User::fromArray($body);
            
            // Insert into database and get the ID
            $insertedId = $this->repository->insert($user->toArray());
            
            // Get the complete user with timestamps
            $createdUser = $this->repository->findById($insertedId);
            
            $data = [
                'success' => true,
                'message' => 'User created successfully',
                'data' => $createdUser
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
        
        // Check if body is null or empty
        if ($body === null || empty($body)) {
            $data = [
                'success' => false,
                'message' => 'Request body is empty or invalid JSON'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Check if user exists
        $existingUser = $this->repository->findById($id);
        if (!$existingUser) {
            $data = [
                'success' => false,
                'message' => 'User not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // Add external_user_id back to body for validation (can't be changed)
        $body['external_user_id'] = $existingUser['external_user_id'];
        
        // Validate using User model
        $errors = User::validate($body);
        
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
            // Check if email is being changed and if new email already exists
            if ($body['email'] !== $existingUser['email']) {
                if ($this->repository->existsByEmail($body['email'])) {
                    $data = [
                        'success' => false,
                        'message' => 'Email already exists'
                    ];
                    
                    $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
                }
            }
            
            // Create User model for validation
            $user = User::fromArray($body);
            
            // Update in database
            $this->repository->update($id, $user->toArray());
            
            // Get updated user
            $updatedUser = $this->repository->findById($id);
            
            $data = [
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $updatedUser
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
        
        // Check if user exists
        $user = $this->repository->findById($id);
        if (!$user) {
            $data = [
                'success' => false,
                'message' => 'User not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // Delete user
        $this->repository->delete($id);
        
        $data = [
            'success' => true,
            'message' => 'User deleted successfully'
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getByRole(Request $request, Response $response, array $args): Response
    {
        $role = $args['role'];
        
        // Validate role
        $validRoles = ['customer', 'driver', 'admin'];
        if (!in_array($role, $validRoles)) {
            $data = [
                'success' => false,
                'message' => 'Invalid role. Must be: customer, driver, or admin'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        $users = $this->repository->findByRole($role);
        
        $data = [
            'success' => true,
            'data' => $users,
            'count' => count($users),
            'role' => $role
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getCustomers(Request $request, Response $response): Response
    {
        $customers = $this->repository->getAllCustomers();
        
        $data = [
            'success' => true,
            'data' => $customers,
            'count' => count($customers)
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getDrivers(Request $request, Response $response): Response
    {
        $drivers = $this->repository->getAllDrivers();
        
        $data = [
            'success' => true,
            'data' => $drivers,
            'count' => count($drivers)
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getAvailableDrivers(Request $request, Response $response): Response
    {
        $drivers = $this->repository->getAvailableDrivers();
        
        $data = [
            'success' => true,
            'data' => $drivers,
            'count' => count($drivers),
            'message' => 'Drivers without assigned cars'
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function updateRole(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $body = $request->getParsedBody();
        
        // Check if user exists
        $user = $this->repository->findById($id);
        if (!$user) {
            $data = [
                'success' => false,
                'message' => 'User not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // Validate role
        if (!isset($body['role'])) {
            $data = [
                'success' => false,
                'message' => 'Role is required'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        $validRoles = ['customer', 'driver', 'admin'];
        if (!in_array($body['role'], $validRoles)) {
            $data = [
                'success' => false,
                'message' => 'Invalid role. Must be: customer, driver, or admin'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Update role
        $this->repository->updateRole($id, $body['role']);
        
        // Get updated user
        $updatedUser = $this->repository->findById($id);
        
        $data = [
            'success' => true,
            'message' => 'User role updated successfully',
            'data' => $updatedUser
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function sync(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        
        // Check if body is null or empty
        if ($body === null || empty($body)) {
            $data = [
                'success' => false,
                'message' => 'Request body is empty or invalid JSON'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Validate using User model
        $errors = User::validate($body);
        
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
            // Sync user from auth service (create or update)
            $userId = $this->repository->syncFromAuthService($body);
            
            // Get the synced user
            $syncedUser = $this->repository->findById($userId);
            
            $data = [
                'success' => true,
                'message' => 'User synced successfully',
                'data' => $syncedUser
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
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
}