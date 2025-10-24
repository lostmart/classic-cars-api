<?php
/**
 * Authentication Middleware (Example)
 * Checks for API key in headers
 * Similar to your Node.js checkApiKey middleware
 */

class AuthMiddleware {
    
    private $apiKey;
    private $excludedRoutes;
    
    /**
     * Constructor
     * @param string $apiKey - The valid API key
     * @param array $excludedRoutes - Routes that don't need auth
     */
    public function __construct($apiKey = 'your-secret-api-key', $excludedRoutes = ['/']) {
        $this->apiKey = $apiKey;
        $this->excludedRoutes = $excludedRoutes;
    }
    
    /**
     * Check authentication
     */
    public function handle($request, $next) {
        // Skip auth for excluded routes
        if (in_array($request['uri'], $this->excludedRoutes)) {
            return $next($request);
        }
        
        // Get API key from headers
        $headers = $request['headers'];
        $providedKey = $headers['X-API-Key'] ?? $headers['x-api-key'] ?? null;
        
        // Check if API key is valid
        if ($providedKey !== $this->apiKey) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Non autorisé: Clé API manquante ou invalide'
            ]);
            exit;
        }
        
        // API key is valid, continue
        return $next($request);
    }
}