<?php

namespace Zecto\Exceptions;

class InvalidRequestError extends ZectoError
{
    public string $detail;

    public function __construct(string $detail)
    {
        $this->detail = $detail;
        parent::__construct($detail);
    }
}
