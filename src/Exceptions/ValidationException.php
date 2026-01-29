<?php

namespace App\Exceptions;

class ValidationException extends ApiException
{
    protected array $errors;

    public function __construct(string $message = "Validation failed", array $errors = [], int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, 400, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
