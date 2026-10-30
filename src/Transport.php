<?php

namespace Zecto;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Zecto\Exceptions\APIConnectionError;
use Zecto\Exceptions\ErrorMapper;

class Transport
{
    public const BASE_URL = 'https://api.zecto.io/whatsapp';
    public const DEFAULT_TIMEOUT = 30.0;

    private Client $client;

    public function __construct(
        string $apiKey,
        float $timeout = self::DEFAULT_TIMEOUT
    ) {
        $this->client = new Client([
            'base_uri' => self::BASE_URL . '/',
            'timeout' => $timeout,
            'http_errors' => false,
            'headers' => [
                'X-API-Key' => $apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function request(
        string $method,
        string $path,
        ?array $params = null,
        mixed $json = null
    ): mixed {
        $options = [];

        $cleaned = $this->cleanParams($params);
        if ($cleaned !== null) {
            $options['query'] = $cleaned;
        }
        if ($json !== null) {
            $options['json'] = $json;
        }

        $relativePath = ltrim($path, '/');

        try {
            $response = $this->client->request($method, $relativePath, $options);
        } catch (ConnectException $e) {
            throw new APIConnectionError($e->getMessage());
        } catch (GuzzleException $e) {
            throw new APIConnectionError($e->getMessage());
        }

        return $this->handleResponse($response, $method, $path);
    }

    private function handleResponse($response, string $method, string $path): mixed
    {
        $status = $response->getStatusCode();
        $text = (string) $response->getBody();

        $body = [];
        if ($text !== '') {
            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        if ($status >= 200 && $status < 300) {
            return $body;
        }

        $rawDetail = $body['detail'] ?? $body['error'] ?? ($text !== '' ? $text : 'Unknown error');
        $detail = $this->formatDetail($rawDetail);
        throw ErrorMapper::forStatus($status, $detail, $body, $method, $path);
    }

    private function cleanParams(?array $params): ?array
    {
        if (empty($params)) {
            return null;
        }
        $out = [];
        foreach ($params as $key => $value) {
            if ($value !== null) {
                $out[$key] = $value;
            }
        }
        return count($out) > 0 ? $out : null;
    }

    private function formatDetail(mixed $detail): string
    {
        if (is_string($detail)) {
            return $detail;
        }
        if (is_array($detail)) {
            $isList = array_is_list($detail);
            if ($isList) {
                $parts = [];
                foreach ($detail as $item) {
                    if (is_array($item)) {
                        $loc = $item['loc'] ?? [];
                        if (count($loc) > 0 && $loc[0] === 'body') {
                            $loc = array_slice($loc, 1);
                        }
                        $field = implode('.', array_map('strval', $loc));
                        $msg = $item['msg'] ?? 'invalid';
                        $parts[] = $field !== '' ? "{$field}: {$msg}" : $msg;
                    } else {
                        $parts[] = (string) $item;
                    }
                }
                return implode('; ', $parts);
            }
        }
        return (string) json_encode($detail);
    }

    public function close(): void
    {
    }
}
