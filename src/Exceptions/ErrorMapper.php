<?php

namespace Zecto\Exceptions;

class ErrorMapper
{
    private const STATUS_MAP = [
        400 => ValidationError::class,
        401 => AuthenticationError::class,
        403 => PermissionDeniedError::class,
        404 => NotFoundError::class,
        409 => ConflictError::class,
        422 => ValidationError::class,
        429 => RateLimitError::class,
        502 => UpstreamError::class,
    ];

    public static function forStatus(
        int $statusCode,
        string $detail,
        array $body = [],
        ?string $requestMethod = null,
        ?string $path = null
    ): APIError {
        $cls = self::STATUS_MAP[$statusCode] ?? null;
        if ($cls === null) {
            $cls = $statusCode >= 500 ? ServerError::class : APIError::class;
        }
        return new $cls($statusCode, $detail, $body, $requestMethod, $path);
    }
}
