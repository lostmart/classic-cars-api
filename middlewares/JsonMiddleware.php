<?php
/**
 * JSON Middleware
 * Parses JSON request bodies and sets JSON response header
 * Equivalent to Express: app.use(express.json())
 */

class JsonMiddleware {
    
    /**
     * Handle JSON parsing and response setup
     */
    public function handle($request, $next) {
        // Set JSON response header
        header('Content-Type: application/json; charset=utf-8');
        
        // Parse JSON body for POST, PUT, PATCH requests
        if (in_array($request['method'], ['POST', 'PUT', 'PATCH'])) {
            $input = file_get_contents('php://input');
            
            if (!empty($input)) {
                $decoded = json_decode($input, true);
                
                // Check for JSON errors
                if (json_last_error() !== JSON_ERROR_NONE) {
                    http_response_code(400);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Invalid JSON: ' . json_last_error_msg()
                    ]);
                    exit;
                }
                
                // Add parsed body to request
                $request['body'] = $decoded;
            } else {
                $request['body'] = [];
            }
        } else {
            $request['body'] = [];
        }
        
        // Continue to next middleware
        return $next($request);
    }
}