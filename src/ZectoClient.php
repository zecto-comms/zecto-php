<?php

namespace Zecto;

use Zecto\WhatsApp\WhatsApp;
use Zecto\Exceptions\InvalidRequestError;

class ZectoClient
{
    private Transport $transport;
    public WhatsApp $whatsapp;

    public function __construct(
        string $apiKey,
        float $timeout = Transport::DEFAULT_TIMEOUT
    ) {
        if ($apiKey === '') {
            throw new InvalidRequestError('apiKey is required');
        }
        $this->transport = new Transport($apiKey, $timeout);
        $this->whatsapp = new WhatsApp($this->transport);
    }

    public function close(): void
    {
        $this->transport->close();
    }
}
