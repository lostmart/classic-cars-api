<?php

use App\Controllers\BookingController;

$bookingController = new BookingController();

// API v1 bookings group
$app->group('/api/v1', function ($group) use ($bookingController) {
    
    // Table initialization (development only)
    $group->get('/bookings/create-table', [$bookingController, 'create']);
    
    // IMPORTANT: Specific routes MUST come BEFORE variable routes like {id}
    $group->get('/bookings/upcoming', [$bookingController, 'getUpcoming']);
    $group->get('/bookings/customer/{customer_id}', [$bookingController, 'getByCustomer']);
    $group->get('/bookings/status/{status}', [$bookingController, 'getByStatus']);
    
    // RESTful CRUD endpoints - {id} routes come AFTER specific routes
    $group->get('/bookings', [$bookingController, 'getAll']);
    $group->post('/bookings', [$bookingController, 'add']);
    $group->get('/bookings/{id}', [$bookingController, 'getById']);           // MOVED AFTER /upcoming
    $group->put('/bookings/{id}', [$bookingController, 'update']);
    $group->delete('/bookings/{id}', [$bookingController, 'delete']);
    
    // ID-specific actions
    $group->patch('/bookings/{id}/status', [$bookingController, 'updateStatus']);
    $group->post('/bookings/{id}/cancel', [$bookingController, 'cancel']);
    
});