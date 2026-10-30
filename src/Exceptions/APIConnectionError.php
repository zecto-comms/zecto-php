<?php

namespace Zecto\Exceptions;

class APIConnectionError extends ZectoError
{
    public function __construct(string $message = 'Could not connect to the Zecto API')
    {
        parent::__construct($message);
    }
}
