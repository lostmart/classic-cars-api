<?php

use App\Controllers\CarController;

$carController = new CarController();

// API v1 cars group
$app->group('/api/v1', function ($group) use ($carController) {
    
    $group->get('/cars/create-table', [$carController, 'create']);
    $group->get('/cars', [$carController, 'getAll']);
    $group->post('/cars/add', [$carController, 'add']);
    $group->get('/cars/{id}', [$carController, 'getById']);
    $group->put('/cars/{id}', [$carController, 'update']);
    $group->delete('/cars/{id}', [$carController, 'delete']);
    
    // Additional car-specific routes
    $group->get('/cars/status/{status}', [$carController, 'getByStatus']);
    $group->get('/cars/driver/{driver_id}', [$carController, 'getByDriverId']);
    $group->patch('/cars/{id}/status', [$carController, 'updateStatus']);
    
});