<?php
/**
 * REST API Entry Point
 * All requests are routed through this file via .htaccess
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error handler to catch unexpected errors
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
    exit;
});

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // CORS - Allow all origins (adjust for production)
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Parse the request URI
// Remove query string if present
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path (adjust this based on your server setup)
// If your app is in root, use: $requestUri
// If in subdirectory like /simple-restPHP/, we need to remove it
$basePath = '/simple-restPHP'; 
$requestUri = str_replace($basePath, '', $requestUri);

// Remove leading and trailing slashes
$requestUri = trim($requestUri, '/');

// Split URI into parts: api/users/5 becomes ['api', 'users', '5']
$uriParts = explode('/', $requestUri);

// Extract endpoint and ID
// Expected format: /api/{endpoint}/{id}
$endpoint = isset($uriParts[1]) ? $uriParts[1] : '';
$id = isset($uriParts[2]) ? $uriParts[2] : null;

// Get request body (for POST and PUT requests)
$input = json_decode(file_get_contents('php://input'), true);


echo json_encode([
    'method' => $method,
    'endpoint' => $endpoint,
]);

// Load configuration
// require_once 'config.php';

// Load helpers
// require_once 'helpers/Response.php';

// Route to appropriate controller based on endpoint
/*
try {
    switch ($endpoint) {
        case 'users':
            // Load the UserController
            require_once 'controllers/UserController.php';
            $controller = new UserController($pdo);
            
            // Route based on HTTP method and whether ID is present
            if ($method === 'GET' && $id === null) {
                // GET /api/users - Get all users
                $controller->index();
            } elseif ($method === 'GET' && $id !== null) {
                // GET /api/users/{id} - Get single user
                $controller->show($id);
            } elseif ($method === 'POST' && $id === null) {
                // POST /api/users - Create new user
                $controller->store($input);
            } elseif ($method === 'PUT' && $id !== null) {
                // PUT /api/users/{id} - Update user
                $controller->update($id, $input);
            } elseif ($method === 'DELETE' && $id !== null) {
                // DELETE /api/users/{id} - Delete user
                $controller->destroy($id);
            } else {
                Response::error('Method not allowed', 405);
            }
            break;
            
        // Add more endpoints here as you build them
        // case 'products':
        //     require_once 'controllers/ProductController.php';
        //     $controller = new ProductController($pdo);
        //     ...
        //     break;
            
        default:
            Response::error('Endpoint not found', 404);
            break;
    }
    
} catch (Exception $e) {
    // Catch any uncaught exceptions and return error response
    Response::error($e->getMessage(), 500);
}
    */