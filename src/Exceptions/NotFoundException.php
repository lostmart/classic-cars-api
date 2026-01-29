<?php

namespace App\Exceptions;

class NotFoundException extends ApiException
{
    public function __construct(string $message = "Resource not found", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, 404, $code, $previous);
    }
}
