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
}