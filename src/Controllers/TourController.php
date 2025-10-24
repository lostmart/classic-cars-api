<?php

namespace App\Controllers;

class TourController
{
    public function create($request, $response)
    {
        $db = $GLOBALS['db'];
        
        // Create tours table
        $db->exec("
            CREATE TABLE IF NOT EXISTS tours (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                description TEXT NOT NULL,
                duration_minutes INTEGER NOT NULL,
                price REAL NOT NULL
            )
        ");
        
        $data = [
            'success' => true,
            'message' => 'Tours table created successfully'
        ];
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function add($request, $response)
    {
        $db = $GLOBALS['db'];
        
        // Insert one tour
        $db->exec("
            INSERT INTO tours (name, description, duration_minutes, price) 
            VALUES ('Romantic Seine Tour', 'Cruise along the Seine passing by Notre-Dame and Eiffel Tower', 60, 120.00)
        ");
        
        $data = [
            'success' => true,
            'message' => 'Tour added successfully'
        ];
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
}