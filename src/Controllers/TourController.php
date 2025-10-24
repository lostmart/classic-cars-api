<?php

namespace App\Controllers;

use App\Repositories\TourRepository;

class TourController
{
    private TourRepository $repository;
    
    public function __construct()
    {
        $this->repository = new TourRepository();
    }
    
    public function create($request, $response)
    {
        $this->repository->createTable();
        
        $data = [
            'success' => true,
            'message' => 'Tours table created successfully'
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function add($request, $response)
    {
        // Get data from request body
        $body = $request->getParsedBody();
        
        // Validate required fields
        if (!isset($body['name']) || !isset($body['description']) || 
            !isset($body['duration_minutes']) || !isset($body['price'])) {
            
            $data = [
                'success' => false,
                'message' => 'Missing required fields: name, description, duration_minutes, price'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Prepare data for insertion
        $tourData = [
            'name' => $body['name'],
            'description' => $body['description'],
            'duration_minutes' => (int) $body['duration_minutes'],
            'price' => (float) $body['price']
        ];
        
        // Insert into database
        $this->repository->insert($tourData);
        
        $data = [
            'success' => true,
            'message' => 'Tour added successfully',
            'data' => $tourData
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }
}