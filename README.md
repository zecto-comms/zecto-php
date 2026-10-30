# Zecto PHP SDK

PHP SDK for the Zecto multi-channel messaging platform.

## Requirements

- PHP 8.1 or newer
- Composer

## Installation

```
composer require zecto/sdk
```

## Quick start

```php
<?php

require 'vendor/autoload.php';

use Zecto\ZectoClient;

$client = new ZectoClient('your-api-key');

$res = $client->whatsapp->messages->sendText(
    'PHONE_NUMBER_ID',
    '923001234567',
    'Hello from Zecto'
);

echo $res['message_id'];
```

## Configuration

The client takes the API key, and optionally a timeout in seconds:

```php
$client = new ZectoClient('your-api-key', 30.0);
```

Named arguments work too:

```php
$client = new ZectoClient('your-api-key', timeout: 60.0);
```

| Argument   | Required | Default                   | Description                     |
| ---------- | -------- | ------------------------- | ------------------------------- |
| `apiKey`   | yes      | none                      | Your Zecto API key              |
| `timeout`  | no       | `30.0`                    | Request timeout in seconds      |

Keep your API key out of source control. Load it from an environment variable or a configuration file that is not committed.

## Messages

All message methods are under `$client->whatsapp->messages`.

```php
$m = $client->whatsapp->messages;

$m->sendText('PID', '923001234567', 'Plain text');
$m->sendText('PID', '923001234567', 'With link https://example.com', true);

$m->sendImage('PID', '923001234567', link: 'https://example.com/image.jpg', caption: 'An image');
$m->sendVideo('PID', '923001234567', link: 'https://example.com/video.mp4');
$m->sendAudio('PID', '923001234567', link: 'https://example.com/audio.mp3');
$m->sendDocument('PID', '923001234567', link: 'https://example.com/file.pdf', filename: 'file.pdf');

$m->sendLocation('PID', '923001234567', 31.5204, 74.3587, name: 'Lahore', address: 'Lahore, Pakistan');

$m->sendContacts('PID', '923001234567', [
    [
        'name' => ['formatted_name' => 'Jane Doe', 'first_name' => 'Jane'],
        'phones' => [['phone' => '+92 300 1234567']],
    ],
]);

$m->sendButtons('PID', '923001234567', 'Please choose:', ['Yes', 'No', 'Maybe'], header: 'Header', footer: 'Footer');

$m->sendList('PID', '923001234567', 'Choose an option', 'Open menu', [
    [
        'title' => 'Section 1',
        'rows' => [
            ['title' => 'Option A', 'description' => 'First option'],
            ['title' => 'Option B', 'description' => 'Second option'],
        ],
    ],
]);

$m->sendCtaUrl('PID', '923001234567', 'Visit our site', 'Open site', 'https://example.com');

$m->sendTemplate('PID', '923001234567', 'TEMPLATE_ID', body: ['value1'], buttons: ['yes', 'PROMO2026']);

$m->markRead('PID', 'MESSAGE_ID');
$m->list(status: 'sent', limit: 20, offset: 0);
$m->get('MESSAGE_ID');
```

For media, provide either `link` or `mediaId`. A maximum of three buttons is allowed.

## Templates

```php
$client->whatsapp->templates->list();
$client->whatsapp->templates->list(status: 'approved', category: 'MARKETING', search: 'otp');
$client->whatsapp->templates->exampleConfig('TEMPLATE_ID');
```

## Numbers

```php
$client->whatsapp->numbers->list();
$client->whatsapp->numbers->get('PHONE_NUMBER_ID');
```

## Account

```php
$client->whatsapp->account->get();
```

## Flows

```php
$client->whatsapp->flows->list();
$client->whatsapp->flows->list('published');
$client->whatsapp->flows->rename('FLOW_ID', 'New name');
$client->whatsapp->flows->setStatus('FLOW_ID', 'published');
$client->whatsapp->flows->trigger('FLOW_ID', '923001234567');
```

## Error handling

All errors extend `Zecto\Exceptions\ZectoError`. API responses map to specific classes by status code.

```php
use Zecto\Exceptions\AuthenticationError;
use Zecto\Exceptions\ValidationError;
use Zecto\Exceptions\RateLimitError;

try {
    $client->whatsapp->messages->sendText('PID', '923001234567', 'Hi');
} catch (AuthenticationError $e) {
    // invalid API key
} catch (ValidationError $e) {
    // malformed request payload
} catch (RateLimitError $e) {
    // slow down
}
```

| Class                   | Trigger                            |
| ----------------------- | ---------------------------------- |
| `InvalidRequestError`   | Client-side validation before send |
| `APIConnectionError`    | Network failure or timeout         |
| `ValidationError`       | HTTP 400 / 422                      |
| `AuthenticationError`   | HTTP 401                            |
| `PermissionDeniedError` | HTTP 403                            |
| `NotFoundError`         | HTTP 404                            |
| `ConflictError`         | HTTP 409                            |
| `RateLimitError`        | HTTP 429                            |
| `UpstreamError`         | HTTP 502                            |
| `ServerError`           | Other 5xx                          |
| `APIError`              | Base class for all HTTP errors     |
| `ZectoError`            | Base class for all SDK errors      |

`APIError` instances expose `$statusCode`, `$detail`, and `$body`.

## Development

```
composer install
vendor/bin/phpunit
```

## License

MIT
