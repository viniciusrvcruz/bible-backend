<?php

namespace App\Exceptions;

use Exception;

abstract class CustomException extends Exception
{
    protected int $statusCode = 500;
    protected string $error;

    public function __construct(string $error, string $message)
    {
        $this->error = $error;
        parent::__construct($message);
    }

    public function getError(): string
    {
        return $this->error;
    }
}