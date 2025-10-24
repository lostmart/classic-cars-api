<?php
// Router for PHP built-in server
if (php_sapi_name() === 'cli-server') {
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . '/public' . $url['path'];
    
    if (is_file($file)) {
        return false; // Serve static file as-is
    }
}

require_once __DIR__ . '/public/index.php';