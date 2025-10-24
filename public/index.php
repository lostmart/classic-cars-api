<?php
/**
 * REST API Entry Point - Classic Cars API
 * All requests are routed through this file via .htaccess
 */

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set error handler
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
    exit;
});

// ==================== LOAD MIDDLEWARE ====================
require_once __DIR__ . '/middlewares/MiddlewareManager.php';
require_once __DIR__ . '/middlewares/CorsMiddleware.php';

// ==================== INITIALIZE MIDDLEWARE ====================
$middlewareManager = new MiddlewareManager();
$middlewareManager->use(new CorsMiddleware());

// ==================== PARSE REQUEST ====================
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/simple-restPHP'; 
$requestUri = str_replace($basePath, '', $requestUri);
$requestUri = trim($requestUri, '/');

$request = [
    'method' => $method,
    'uri' => $requestUri,
    'query' => $_GET,
    'headers' => getallheaders(),
];

// ==================== REQUEST HANDLER WITH ROUTER ====================
$handler = function($request) use ($requestUri) {
    // Set JSON header
    header('Content-Type: application/json; charset=utf-8');
    
    // Load Router class
    require_once __DIR__ . '/Router.php';
    
    // Load routes definition
    $router = require __DIR__ . '/routes.php';
    
    // Dispatch to matched route
    $router->dispatch($request['method'], '/' . $requestUri);
};

// ==================== RUN MIDDLEWARE CHAIN ====================
$middlewareManager->run($request, $handler);