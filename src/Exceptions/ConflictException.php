<?php

namespace App\Exceptions;

class ConflictException extends ApiException
{
    public function __construct(string $message = "Resource conflict", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, 409, $code, $previous);
    }
}
