<?php
/**
 * REST API Entry Point - Classic Cars API
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

// ==================== LOAD MIDDLEWARE SYSTEM ====================
require_once __DIR__ . '/middlewares/MiddlewareManager.php';
require_once __DIR__ . '/middlewares/CorsMiddleware.php';
require_once __DIR__ . '/middlewares/JsonMiddleware.php';
// require_once __DIR__ . '/middlewares/AuthMiddleware.php'; // Uncomment to add auth

// ==================== INITIALIZE MIDDLEWARE ====================
// Create middleware manager
$middlewareManager = new MiddlewareManager();

// Register global middleware (equivalent to Express app.use())
$middlewareManager
    ->use(new CorsMiddleware());      // app.use(cors())
    //->use(new JsonMiddleware());     // app.use(express.json())
    // ->use(new AuthMiddleware('secret-key', ['/'])); // Optional: add auth

// ==================== PARSE REQUEST ====================
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/simple-restPHP'; 
$requestUri = str_replace($basePath, '', $requestUri);
$requestUri = trim($requestUri, '/');

// Build request object to pass through middleware
$request = [
    'method' => $method,
    'uri' => $requestUri,
    'query' => $_GET,
    'headers' => getallheaders(),
];

// ==================== DEFINE REQUEST HANDLER ====================
// This is the final handler that runs after all middleware
$handler = function($request) use ($requestUri) {
    // Split URI into parts
    $uriParts = explode('/', $requestUri);
    $endpoint = isset($uriParts[1]) ? $uriParts[1] : '';
    $id = isset($uriParts[2]) ? $uriParts[2] : null;
    
    // Get parsed body from JsonMiddleware
    $input = $request['body'] ?? null;
    
    // Load configuration (when ready)
    // require_once 'config.php';
    
    // ==================== ROUTING ====================
    try {
        switch ($endpoint) {
            case 'users':
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Users endpoint reached via clean URL!',
                    'method' => $request['method'],
                    'endpoint' => $endpoint,
                    'id' => $id,
                    'body' => $input,
                    'note' => 'Middleware is working! 🎉'
                ], JSON_PRETTY_PRINT);
                
                // Later we'll uncomment this to use the actual controller:
                // require_once 'controllers/UserController.php';
                // $controller = new UserController($pdo);
                // 
                // if ($request['method'] === 'GET' && $id === null) {
                //     $controller->index();
                // } elseif ($request['method'] === 'GET' && $id !== null) {
                //     $controller->show($id);
                // } elseif ($request['method'] === 'POST' && $id === null) {
                //     $controller->store();
                // } elseif ($request['method'] === 'PUT' && $id !== null) {
                //     $controller->update($id);
                // } elseif ($request['method'] === 'DELETE' && $id !== null) {
                //     $controller->destroy($id);
                // }
                break;
                
            case '':
                // Root endpoint - API documentation
                // Equivalent to: app.get('/', (req, res) => { ... })
                echo json_encode([
                    'message' => 'Bienvenue sur l\'API de gestion de voitures classiques',
                    'version' => '1.0.0',
                    'status' => 'success',
                    'endpoints' => [
                        'GET /' => 'Informations sur l\'API',
                        'GET /api/users' => 'Liste des utilisateurs',
                        'GET /api/users/:id' => 'Détails d\'un utilisateur',
                        'POST /api/users' => 'Créer un utilisateur',
                        'PUT /api/users/:id' => 'Modifier un utilisateur',
                        'DELETE /api/users/:id' => 'Supprimer un utilisateur'
                    ],
                    'middleware' => [
                        'CORS' => 'enabled',
                        'JSON parsing' => 'enabled'
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
};

// ==================== RUN MIDDLEWARE CHAIN ====================
// Execute: CorsMiddleware → JsonMiddleware → handler
$middlewareManager->run($request, $handler);