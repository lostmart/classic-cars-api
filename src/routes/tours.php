<?php

use App\Controllers\TourController;

$tourController = new TourController();

// API v1 tours group
$app->group('/api/v1', function ($group) use ($tourController) {
    
    $group->get('/tours/create-table', [$tourController, 'create']);
    $group->post('/tours/add', [$tourController, 'add']);
    
});