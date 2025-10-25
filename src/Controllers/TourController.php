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
}