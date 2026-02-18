<?php

namespace App\Exceptions;

use Exception;

class MovieDatabaseException extends Exception
{
    public function __construct(string $message = 'Could not connect to movie database. Please try again later.')
    {
        parent::__construct($message);
    }
}
