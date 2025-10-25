<?php

use App\Controllers\UserController;

$userController = new UserController();

// API v1 users group
$app->group('/api/v1', function ($group) use ($userController) {
    
    // Table initialization (development only)
    $group->get('/users/create-table', [$userController, 'create']);
    
    // IMPORTANT: Specific routes MUST come BEFORE variable routes like {id}
    $group->get('/users/customers', [$userController, 'getCustomers']);
    $group->get('/users/drivers', [$userController, 'getDrivers']);
    $group->get('/users/drivers/available', [$userController, 'getAvailableDrivers']);
    $group->get('/users/role/{role}', [$userController, 'getByRole']);
    
    // RESTful CRUD endpoints - {id} routes come AFTER specific routes
    $group->get('/users', [$userController, 'getAll']);
    $group->post('/users', [$userController, 'add']);
    $group->get('/users/{id}', [$userController, 'getById']);
    $group->put('/users/{id}', [$userController, 'update']);
    $group->delete('/users/{id}', [$userController, 'delete']);
    
    // ID-specific actions
    $group->patch('/users/{id}/role', [$userController, 'updateRole']);
    
});

// Internal endpoint for auth service synchronization
$app->group('/api/v1/internal', function ($group) use ($userController) {
    $group->post('/users/sync', [$userController, 'sync']);
});