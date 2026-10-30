<?php

namespace Zecto\Exceptions;

class APIError extends ZectoError
{
    public int $statusCode;
    public string $detail;
    public array $body;
    public ?string $requestMethod;
    public ?string $path;

    public function __construct(
        int $statusCode,
        string $detail,
        array $body = [],
        ?string $requestMethod = null,
        ?string $path = null
    ) {
        $this->statusCode = $statusCode;
        $this->detail = $detail;
        $this->body = $body;
        $this->requestMethod = $requestMethod;
        $this->path = $path;
        parent::__construct("[{$statusCode}] {$detail}");
    }
}
