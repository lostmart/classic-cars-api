<?php
/**
 * CORS Middleware
 * Handles Cross-Origin Resource Sharing
 * Equivalent to Express: app.use(cors())
 */

class CorsMiddleware {
    
    private $allowedOrigins;
    private $allowedMethods;
    private $allowedHeaders;
    
    /**
     * Constructor
     * @param array $options - CORS configuration
     */
    public function __construct($options = []) {
        // Default CORS settings
        $this->allowedOrigins = $options['origins'] ?? '*';
        $this->allowedMethods = $options['methods'] ?? 'GET, POST, PUT, DELETE, OPTIONS';
        $this->allowedHeaders = $options['headers'] ?? 'Content-Type, Authorization';
    }
    
    /**
     * Handle CORS headers
     */
    public function handle($request, $next) {
        // Set CORS headers
        header('Access-Control-Allow-Origin: ' . $this->allowedOrigins);
        header('Access-Control-Allow-Methods: ' . $this->allowedMethods);
        header('Access-Control-Allow-Headers: ' . $this->allowedHeaders);
        header('Access-Control-Max-Age: 86400'); // 24 hours
        
        // Handle preflight OPTIONS request
        if ($request['method'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // Continue to next middleware
        return $next($request);
    }
}