<?php

namespace App\Application\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public function __construct(string $message = "Validation error", int $code = 400, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
