<?php

namespace Zecto\WhatsApp;

use Zecto\Transport;
use Zecto\Exceptions\InvalidRequestError;

class Flows
{
    public function __construct(private Transport $transport)
    {
    }

    public function list(?string $status = null): mixed
    {
        return $this->transport->request('GET', '/v1/flows', ['status' => $status]);
    }

    public function rename(string $flowId, string $name): array
    {
        if ($flowId === '') {
            throw new InvalidRequestError('flowId is required');
        }
        if ($name === '') {
            throw new InvalidRequestError('name is required');
        }
        return $this->transport->request('POST', '/v1/flows/' . rawurlencode($flowId) . '/rename', null, ['name' => $name]);
    }

    public function setStatus(string $flowId, string $status): array
    {
        if ($flowId === '') {
            throw new InvalidRequestError('flowId is required');
        }
        if ($status === '') {
            throw new InvalidRequestError('status is required');
        }
        return $this->transport->request('POST', '/v1/flows/' . rawurlencode($flowId) . '/status', null, ['status' => $status]);
    }

    public function trigger(string $flowId, string $to): array
    {
        if ($flowId === '') {
            throw new InvalidRequestError('flowId is required');
        }
        if ($to === '') {
            throw new InvalidRequestError('to is required');
        }
        return $this->transport->request('POST', '/v1/flows/' . rawurlencode($flowId) . '/trigger', null, ['to' => $to]);
    }
}
