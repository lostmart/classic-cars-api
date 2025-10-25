<?php

namespace App\Controllers;

use App\Repositories\BookingRepository;
use App\Repositories\TourRepository;
use App\Repositories\CarRepository;
use App\Models\Booking;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class BookingController
{
    private BookingRepository $repository;
    private TourRepository $tourRepository;
    private CarRepository $carRepository;
    
    public function __construct()
    {
        $this->repository = new BookingRepository();
        $this->tourRepository = new TourRepository();
        $this->carRepository = new CarRepository();
    }
    
    public function create(Request $request, Response $response): Response
    {
        $this->repository->createTable();
        
        $data = [
            'success' => true,
            'message' => 'Bookings table created successfully'
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getAll(Request $request, Response $response): Response
    {
        $bookings = $this->repository->findAll();
        
        $data = [
            'success' => true,
            'data' => $bookings,
            'count' => count($bookings)
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getById(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $booking = $this->repository->findById($id);
        
        if (!$booking) {
            $data = [
                'success' => false,
                'message' => 'Booking not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        $data = [
            'success' => true,
            'data' => $booking
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function add(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();
        
        // Check if body is null or empty
        if ($body === null || empty($body)) {
            $data = [
                'success' => false,
                'message' => 'Request body is empty or invalid JSON'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Validate using Booking model
        $errors = Booking::validate($body);
        
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
            // Verify tour exists
            $tour = $this->tourRepository->findById($body['tour_id']);
            if (!$tour) {
                $data = [
                    'success' => false,
                    'message' => 'Tour not found'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            // Verify car exists and is available
            $car = $this->carRepository->findById($body['car_id']);
            if (!$car) {
                $data = [
                    'success' => false,
                    'message' => 'Car not found'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            if ($car['status'] !== 'available') {
                $data = [
                    'success' => false,
                    'message' => 'Car is not available for booking'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            
            // Check if passenger count exceeds car capacity
            if ($body['passenger_count'] > $car['capacity']) {
                $data = [
                    'success' => false,
                    'message' => "Passenger count exceeds car capacity of {$car['capacity']}"
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
            // Check for conflicting bookings
            if ($this->repository->hasConflictingBooking($body['car_id'], $body['booking_date'], $body['booking_time'])) {
                $data = [
                    'success' => false,
                    'message' => 'Car is already booked at this date and time'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            
            // Create Booking model
            $booking = Booking::fromArray($body);
            
            // Insert into database and get the ID
            $insertedId = $this->repository->insert($booking->toArray());
            
            // Get the complete booking with timestamps
            $createdBooking = $this->repository->findById($insertedId);
            
            $data = [
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $createdBooking
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
    
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $body = $request->getParsedBody();
        
        // Check if body is null or empty
        if ($body === null || empty($body)) {
            $data = [
                'success' => false,
                'message' => 'Request body is empty or invalid JSON'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Check if booking exists
        $existingBooking = $this->repository->findById($id);
        if (!$existingBooking) {
            $data = [
                'success' => false,
                'message' => 'Booking not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // Can't update completed or cancelled bookings
        if (in_array($existingBooking['status'], ['completed', 'cancelled'])) {
            $data = [
                'success' => false,
                'message' => "Cannot update {$existingBooking['status']} bookings"
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Validate using Booking model
        $errors = Booking::validate($body);
        
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
            // Verify tour exists
            $tour = $this->tourRepository->findById($body['tour_id']);
            if (!$tour) {
                $data = [
                    'success' => false,
                    'message' => 'Tour not found'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            // Verify car exists and check capacity
            $car = $this->carRepository->findById($body['car_id']);
            if (!$car) {
                $data = [
                    'success' => false,
                    'message' => 'Car not found'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            if ($body['passenger_count'] > $car['capacity']) {
                $data = [
                    'success' => false,
                    'message' => "Passenger count exceeds car capacity of {$car['capacity']}"
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
            // Check for conflicting bookings (exclude current booking)
            if ($this->repository->hasConflictingBooking(
                $body['car_id'], 
                $body['booking_date'], 
                $body['booking_time'],
                $id
            )) {
                $data = [
                    'success' => false,
                    'message' => 'Car is already booked at this date and time'
                ];
                
                $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            
            // Create Booking model for validation
            $booking = Booking::fromArray($body);
            
            // Update in database
            $this->repository->update($id, $booking->toArray());
            
            // Get updated booking
            $updatedBooking = $this->repository->findById($id);
            
            $data = [
                'success' => true,
                'message' => 'Booking updated successfully',
                'data' => $updatedBooking
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json');
            
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
    
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Check if booking exists
        $booking = $this->repository->findById($id);
        if (!$booking) {
            $data = [
                'success' => false,
                'message' => 'Booking not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // Delete booking
        $this->repository->delete($id);
        
        $data = [
            'success' => true,
            'message' => 'Booking deleted successfully'
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getByCustomer(Request $request, Response $response, array $args): Response
    {
        $customerId = (int) $args['customer_id'];
        $bookings = $this->repository->findByCustomerId($customerId);
        
        $data = [
            'success' => true,
            'data' => $bookings,
            'count' => count($bookings)
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getByStatus(Request $request, Response $response, array $args): Response
    {
        $status = $args['status'];
        
        // Validate status
        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            $data = [
                'success' => false,
                'message' => 'Invalid status. Must be: pending, confirmed, completed, or cancelled'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        $bookings = $this->repository->findByStatus($status);
        
        $data = [
            'success' => true,
            'data' => $bookings,
            'count' => count($bookings),
            'status' => $status
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function getUpcoming(Request $request, Response $response): Response
    {
        $bookings = $this->repository->findUpcoming();
        
        $data = [
            'success' => true,
            'data' => $bookings,
            'count' => count($bookings)
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $body = $request->getParsedBody();
        
        // Check if booking exists
        $booking = $this->repository->findById($id);
        if (!$booking) {
            $data = [
                'success' => false,
                'message' => 'Booking not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // Validate status
        if (!isset($body['status'])) {
            $data = [
                'success' => false,
                'message' => 'Status is required'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($body['status'], $validStatuses)) {
            $data = [
                'success' => false,
                'message' => 'Invalid status. Must be: pending, confirmed, completed, or cancelled'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Business rule: Can't change status of completed bookings
        if ($booking['status'] === 'completed') {
            $data = [
                'success' => false,
                'message' => 'Cannot change status of completed bookings'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Update status
        $this->repository->updateStatus($id, $body['status']);
        
        // Get updated booking
        $updatedBooking = $this->repository->findById($id);
        
        $data = [
            'success' => true,
            'message' => 'Booking status updated successfully',
            'data' => $updatedBooking
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function cancel(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        
        // Check if booking exists
        $booking = $this->repository->findById($id);
        if (!$booking) {
            $data = [
                'success' => false,
                'message' => 'Booking not found'
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
        
        // Can only cancel pending or confirmed bookings
        if (!in_array($booking['status'], ['pending', 'confirmed'])) {
            $data = [
                'success' => false,
                'message' => "Cannot cancel {$booking['status']} bookings"
            ];
            
            $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        
        // Update status to cancelled
        $this->repository->updateStatus($id, 'cancelled');
        
        // Get updated booking
        $updatedBooking = $this->repository->findById($id);
        
        $data = [
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => $updatedBooking
        ];
        
        $response->getBody()->write(json_encode($data, JSON_PRETTY_PRINT));
        return $response->withHeader('Content-Type', 'application/json');
    }
}