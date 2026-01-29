<?php

namespace App;

use Throwable;

class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }

    public static function handleException(Throwable $exception): void
    {
        self::logError($exception);
        self::sendJsonResponse($exception);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        // Convert errors to exceptions
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleFatalError(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
            $exception = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            self::handleException($exception);
        }
    }

    private static function sendJsonResponse(Throwable $exception): void
    {
        http_response_code(self::getHttpStatusCode($exception));

        $response = [
            'status' => 'error',
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        
        if ($exception instanceof \App\Exceptions\ValidationException) {
            $response['errors'] = $exception->getErrors();
        }

        // Only show file and line in development for security
        if (getenv('APP_ENV') !== 'production') {
            $response['file'] = $exception->getFile();
            $response['line'] = $exception->getLine();
            $response['trace'] = $exception->getTrace();
        }

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit();
    }

    private static function logError(Throwable $exception): void
    {
        // Placeholder for structured logging as per GEMINI.md
        // In a real application, this would integrate with a logging library (e.g., Monolog)
        error_log(sprintf(
            "[%s] Uncaught exception: %s in %s:%d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        ));
    }

    private static function getHttpStatusCode(Throwable $exception): int
    {
        if ($exception instanceof \App\Exceptions\ApiException) {
            return $exception->getStatusCode();
        } elseif ($exception instanceof \InvalidArgumentException || $exception instanceof \UnexpectedValueException) {
            return 400; // Bad Request
        } elseif ($exception instanceof \RuntimeException) {
            return 500; // Internal Server Error
        }

        return 500; // Default to Internal Server Error
    }
}
