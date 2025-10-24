<?php

use App\Controllers\TourController;

// Define all your routes here

$app->get('/hello', function ($request, $response) {
    $data = [
        'message' => 'Hello World',
        'app_name' => $_ENV['APP_NAME']  // NEW!
    ];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/goodbye', function ($request, $response) {
    $data = ['message' => 'Goodbye World'];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});


// Controller routes - NEW!
$tourController = new TourController();

$app->get('/create-tours', [$tourController, 'create']);
$app->get('/add-tour', [$tourController, 'add']);