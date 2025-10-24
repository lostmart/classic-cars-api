<?php

use App\Controllers\TourController;

// API v1 group
$app->group('/api/v1', function ($group) {
    
    // Welcome endpoint
    $group->get('', function ($request, $response) {
        $data = [
            'success' => true,
            'message' => 'Welcome to the Paris Classic Tours API',
            'app_name' => getenv('APP_NAME') ?: $_ENV['APP_NAME'] ?? 'Paris Classic Tours API',
            'version' => 'v1',
            'timestamp' => date('c')
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Health check endpoint
    $group->get('/health', function ($request, $response) {
        $data = [
            'success' => true,
            'status' => 'healthy',
            'database' => 'connected',
            'timestamp' => date('c')
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    });
    
});