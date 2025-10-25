<?php

namespace App\Controllers;

use App\Repositories\TourRepository;
use App\Models\Tour;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TourController
{
    private TourRepository $repository;
    
    public function __construct()
    {
        $this->repository = new TourRepository();
    }

    // Get all tours
    public function getAll(Request $request, Response $response): Response
    {
        $tours = $this->repository->findAll();
        
        $data = [
            'success' => true,
            'data' => $tours
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    // Create tours table
    public function create(Request $request, Response $response): Response
    {
        $this->repository->createTable();
        
        $data = [
            'success' => true,
            'message' => 'Tours table created successfully'
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    // Add a new tour
    public function add(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        
        // Validate using Tour model
        $errors = Tour::validate($body);
        
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
            // Create Tour model
            $tour = Tour::fromArray($body);
            
            // Insert into database and get the ID
            $insertedId = $this->repository->insert($tour->toArray());
            
            // Create response data with the inserted ID
            $tourData = $tour->toArray();
            $tourData['id'] = $insertedId;
            
            $data = [
                'success' => true,
                'message' => 'Tour added successfully',
                'data' => $tourData
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

    // Get tour by ID
    public function getById(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $tourData = $this->repository->findById($id);
        if ($tourData) {
            $data = [
                'success' => true,
                'data' => $tourData
            ];
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $data = [
                'success' => false,
                'message' => 'Tour not found'
            ];
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }

    // Update tour by ID
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $body = $request->getParsedBody();
        // Validate using Tour model
        $errors = Tour::validate($body);
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
            // Create Tour model
            $tour = Tour::fromArray(array_merge($body, ['id' => $id]));
            // Update in database
            $this->repository->update($id, $tour->toArray());
            $data = [
                'success' => true,
                'message' => 'Tour updated successfully',
                'data' => $tour->toArray()
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

    // Delete tour by ID
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $tourData = $this->repository->findById($id);
        if ($tourData) {
            $this->repository->delete($id);
            $data = [
                'success' => true,
                'message' => 'Tour deleted successfully'
            ];
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $data = [
                'success' => false,
                'message' => 'Tour not found'
            ];
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    }
}