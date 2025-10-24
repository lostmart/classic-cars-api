<?php
/**
 * Middleware Manager
 * Manages and executes middleware stack
 * Similar to Express app.use() functionality
 */

class MiddlewareManager {
    
    private $middlewares = [];
    
    /**
     * Add middleware to the stack
     * Equivalent to Express: app.use(middleware)
     * @param object $middleware - Any object with a handle() method
     */
    public function use($middleware) {
        $this->middlewares[] = $middleware;
        return $this; // Allow chaining: $manager->use()->use()
    }
    
    /**
     * Run all middleware in sequence
     * @param array $request - Request data
     * @param callable $finalHandler - Final handler (controller)
     */
    public function run($request, $finalHandler) {
        // Create the middleware chain
        $chain = $this->createChain($finalHandler);
        
        // Execute the chain
        return $chain($request);
    }
    
    /**
     * Create middleware execution chain
     * Each middleware calls next(), creating a chain
     */
    private function createChain($finalHandler) {
        // Start with the final handler
        $next = $finalHandler;
        
        // Wrap each middleware around the next one (reverse order)
        foreach (array_reverse($this->middlewares) as $middleware) {
            $next = function($request) use ($middleware, $next) {
                return $middleware->handle($request, $next);
            };
        }
        
        return $next;
    }
    
    /**
     * Get all registered middleware (for debugging)
     */
    public function getMiddlewares() {
        return $this->middlewares;
    }
}