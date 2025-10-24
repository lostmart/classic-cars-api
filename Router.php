<?php
/**
 * Simple Router Class
 * Handles route registration and matching for the REST API
 */

class Router {
    private $routes = [];
    
    /**
     * Register a GET route
     * @param string $path - The URL path
     * @param callable $callback - The function to execute
     */
    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
    }
    
    /**
     * Register a POST route
     * @param string $path - The URL path
     * @param callable $callback - The function to execute
     */
    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
    }
    
    /**
     * Register a PUT route
     * @param string $path - The URL path
     * @param callable $callback - The function to execute
     */
    public function put($path, $callback) {
        $this->addRoute('PUT', $path, $callback);
    }
    
    /**
     * Register a DELETE route
     * @param string $path - The URL path
     * @param callable $callback - The function to execute
     */
    public function delete($path, $callback) {
        $this->addRoute('DELETE', $path, $callback);
    }
    
    /**
     * Add a route to the routes array
     * @param string $method - HTTP method
     * @param string $path - URL path
     * @param callable $callback - Function to execute
     */
    private function addRoute($method, $path, $callback) {
        // Convert route path to regex pattern
        // Example: /api/users/:id becomes /api/users/([^/]+)
        $pattern = preg_replace('/\/:([^\/]+)/', '/([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }
    
    /**
     * Match the current request to a registered route and execute it
     * @param string $method - HTTP method
     * @param string $uri - Request URI
     */
    public function dispatch($method, $uri) {
        foreach ($this->routes as $route) {
            // Check if method matches
            if ($route['method'] !== $method) {
                continue;
            }
            
            // Check if path matches using regex
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Remove the full match, keep only captured groups (parameters)
                array_shift($matches);
                
                // Execute the callback with parameters
                call_user_func_array($route['callback'], $matches);
                return true;
            }
        }
        
        // No route matched
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'Route not found'
        ]);
        return false;
    }
    
    /**
     * Get all registered routes (useful for debugging)
     * @return array
     */
    public function getRoutes() {
        return $this->routes;
    }
}