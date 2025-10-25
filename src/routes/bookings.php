<?php

use App\Controllers\BookingController;

$bookingController = new BookingController();

// API v1 bookings group
$app->group('/api/v1', function ($group) use ($bookingController) {
    
    // Table initialization (development only)
    $group->get('/bookings/create-table', [$bookingController, 'create']);
    
    // RESTful CRUD endpoints
    $group->get('/bookings', [$bookingController, 'getAll']);
    $group->post('/bookings', [$bookingController, 'add']);
    $group->get('/bookings/{id}', [$bookingController, 'getById']);
    $group->put('/bookings/{id}', [$bookingController, 'update']);
    $group->delete('/bookings/{id}', [$bookingController, 'delete']);
    
    // Additional booking-specific routes
    $group->get('/bookings/customer/{customer_id}', [$bookingController, 'getByCustomer']);
    $group->get('/bookings/status/{status}', [$bookingController, 'getByStatus']);
    $group->get('/bookings/upcoming', [$bookingController, 'getUpcoming']);
    $group->patch('/bookings/{id}/status', [$bookingController, 'updateStatus']);
    $group->post('/bookings/{id}/cancel', [$bookingController, 'cancel']);
    
});