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
header('Access-Control-Allow-Origin: *');
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
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path
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

// Load configuration (we'll create this next)
// require_once 'config.php';

// Route to appropriate controller based on endpoint
try {
    switch ($endpoint) {
        case 'users':
            echo json_encode([
                'status' => 'success',
                'message' => 'Users endpoint reached via clean URL!',
                'method' => $method,
                'endpoint' => $endpoint,
                'id' => $id,
                'note' => '.htaccess is working! 🎉'
            ], JSON_PRETTY_PRINT);
            
            // Later we'll uncomment this to use the actual controller:
            // require_once 'controllers/UserController.php';
            // $controller = new UserController($pdo);
            // 
            // if ($method === 'GET' && $id === null) {
            //     $controller->index();
            // } elseif ($method === 'GET' && $id !== null) {
            //     $controller->show($id);
            // } elseif ($method === 'POST' && $id === null) {
            //     $controller->store();
            // } elseif ($method === 'PUT' && $id !== null) {
            //     $controller->update($id);
            // } elseif ($method === 'DELETE' && $id !== null) {
            //     $controller->destroy($id);
            // }
            break;
            
        case '':
            // Root endpoint - API documentation
            echo json_encode([
                'status' => 'success',
                'message' => 'Welcome to Classic Cars REST API',
                'version' => '1.0',
                'endpoints' => [
                    'GET /api/users' => 'Get all users',
                    'GET /api/users/:id' => 'Get single user',
                    'POST /api/users' => 'Create user',
                    'PUT /api/users/:id' => 'Update user',
                    'DELETE /api/users/:id' => 'Delete user'
                ]
            ], JSON_PRETTY_PRINT);
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Endpoint not found',
                'requested' => $endpoint,
                'available' => ['users']
            ], JSON_PRETTY_PRINT);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}