<?php

namespace App\Application\Exceptions;

use Exception;

class ForbiddenException extends Exception
{
    public function __construct(string $message = "Access forbidden", int $code = 403, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
