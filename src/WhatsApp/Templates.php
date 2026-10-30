<?php

namespace Zecto\WhatsApp;

use Zecto\Transport;
use Zecto\Exceptions\InvalidRequestError;

class Templates
{
    public function __construct(private Transport $transport)
    {
    }

    public function list(
        ?string $status = null,
        ?string $category = null,
        ?string $search = null
    ): mixed {
        $params = ['status' => $status, 'category' => $category, 'search' => $search];
        return $this->transport->request('GET', '/v1/templates', $params);
    }

    public function exampleConfig(string $templateId): array
    {
        if ($templateId === '') {
            throw new InvalidRequestError('templateId is required');
        }
        return $this->transport->request('GET', '/v1/templates/example-config/' . rawurlencode($templateId));
    }
}
