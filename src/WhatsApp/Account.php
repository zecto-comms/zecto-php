<?php

namespace Zecto\WhatsApp;

use Zecto\Transport;

class Account
{
    public function __construct(private Transport $transport)
    {
    }

    public function get(): array
    {
        return $this->transport->request('GET', '/v1/account');
    }
}
