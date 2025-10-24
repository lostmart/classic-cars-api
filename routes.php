<?php
/**
 * API Routes Definition
 * Define all your routes here - similar to Express router
 */

// Create router instance
$router = new Router();

// ==================== ROOT ROUTE ====================
// GET / - API Welcome/Documentation
$router->get('/', function() {
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
            'Router' => 'enabled'
        ]
    ], JSON_PRETTY_PRINT);
});

// ==================== USER ROUTES ====================

// GET /api/users - Get all users
$router->get('/api/users', function() {
    echo json_encode([
        'status' => 'success',
        'message' => 'Getting all users',
        'data' => []
    ], JSON_PRETTY_PRINT);
    
    // TODO: Connect to controller
    // require_once __DIR__ . '/controllers/UserController.php';
    // $controller = new UserController($pdo);
    // $controller->index();
});

// GET /api/users/:id - Get single user
$router->get('/api/users/:id', function($id) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Getting user by ID',
        'id' => $id,
        'data' => null
    ], JSON_PRETTY_PRINT);
    
    // TODO: Connect to controller
    // require_once __DIR__ . '/controllers/UserController.php';
    // $controller = new UserController($pdo);
    // $controller->show($id);
});

// POST /api/users - Create new user
$router->post('/api/users', function() {
    // Get JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Creating new user',
        'received_data' => $input
    ], JSON_PRETTY_PRINT);
    
    // TODO: Connect to controller
    // require_once __DIR__ . '/controllers/UserController.php';
    // $controller = new UserController($pdo);
    // $controller->store();
});

// PUT /api/users/:id - Update user
$router->put('/api/users/:id', function($id) {
    // Get JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Updating user',
        'id' => $id,
        'received_data' => $input
    ], JSON_PRETTY_PRINT);
    
    // TODO: Connect to controller
    // require_once __DIR__ . '/controllers/UserController.php';
    // $controller = new UserController($pdo);
    // $controller->update($id);
});

// DELETE /api/users/:id - Delete user
$router->delete('/api/users/:id', function($id) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Deleting user',
        'id' => $id
    ], JSON_PRETTY_PRINT);
    
    // TODO: Connect to controller
    // require_once __DIR__ . '/controllers/UserController.php';
    // $controller = new UserController($pdo);
    // $controller->destroy($id);
});

// Return the router instance
return $router;