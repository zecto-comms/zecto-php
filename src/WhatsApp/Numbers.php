<?php

namespace Zecto\WhatsApp;

use Zecto\Transport;
use Zecto\Exceptions\InvalidRequestError;

class Numbers
{
    public function __construct(private Transport $transport)
    {
    }

    public function list(): mixed
    {
        return $this->transport->request('GET', '/v1/numbers');
    }

    public function get(string $phoneNumberId): array
    {
        if ($phoneNumberId === '') {
            throw new InvalidRequestError('phoneNumberId is required');
        }
        return $this->transport->request('GET', '/v1/numbers/' . rawurlencode($phoneNumberId));
    }
}
