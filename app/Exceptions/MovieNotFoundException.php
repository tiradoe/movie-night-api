<?php

namespace App\Exceptions;

use Exception;

class MovieNotFoundException extends Exception
{
    public function __construct(string $message = 'Movie not found')
    {
        parent::__construct($message);
    }
}
