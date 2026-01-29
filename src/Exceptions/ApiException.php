<?php

namespace App\Exceptions;

use Throwable;

class ApiException extends \Exception
{
    protected int $statusCode;

    public function __construct(string $message = "", int $statusCode = 500, int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
