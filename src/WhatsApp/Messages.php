<?php

namespace Zecto\WhatsApp;

use Zecto\Transport;
use Zecto\Payloads;
use Zecto\Exceptions\InvalidRequestError;

class Messages
{
    public function __construct(private Transport $transport)
    {
    }

    public function send(array $payload): array
    {
        return $this->transport->request('POST', '/v1/messages/send', null, $payload);
    }

    public function sendText(
        string $phoneNumberId,
        string $to,
        string $body,
        bool $previewUrl = false,
        ?string $replyTo = null
    ): array {
        return $this->send(Payloads::text($phoneNumberId, $to, $body, $previewUrl, $replyTo));
    }

    public function sendImage(
        string $phoneNumberId,
        string $to,
        ?string $link = null,
        ?string $mediaId = null,
        ?string $caption = null,
        ?string $replyTo = null
    ): array {
        return $this->send(Payloads::image($phoneNumberId, $to, $link, $mediaId, $caption, $replyTo));
    }

    public function sendVideo(
        string $phoneNumberId,
        string $to,
        ?string $link = null,
        ?string $mediaId = null,
        ?string $caption = null,
        ?string $replyTo = null
    ): array {
        return $this->send(Payloads::video($phoneNumberId, $to, $link, $mediaId, $caption, $replyTo));
    }

    public function sendAudio(
        string $phoneNumberId,
        string $to,
        ?string $link = null,
        ?string $mediaId = null,
        ?string $replyTo = null
    ): array {
        return $this->send(Payloads::audio($phoneNumberId, $to, $link, $mediaId, $replyTo));
    }

    public function sendDocument(
        string $phoneNumberId,
        string $to,
        ?string $link = null,
        ?string $mediaId = null,
        ?string $caption = null,
        ?string $filename = null,
        ?string $replyTo = null
    ): array {
        return $this->send(
            Payloads::document($phoneNumberId, $to, $link, $mediaId, $caption, $filename, $replyTo)
        );
    }

    public function sendLocation(
        string $phoneNumberId,
        string $to,
        float $latitude,
        float $longitude,
        ?string $name = null,
        ?string $address = null,
        ?string $replyTo = null
    ): array {
        return $this->send(
            Payloads::location($phoneNumberId, $to, $latitude, $longitude, $name, $address, $replyTo)
        );
    }

    public function sendContacts(
        string $phoneNumberId,
        string $to,
        array $contacts,
        ?string $replyTo = null
    ): array {
        return $this->send(Payloads::contacts($phoneNumberId, $to, $contacts, $replyTo));
    }

    public function sendButtons(
        string $phoneNumberId,
        string $to,
        string $body,
        array $buttons,
        ?string $header = null,
        ?string $footer = null,
        ?string $replyTo = null
    ): array {
        return $this->send(
            Payloads::interactiveButtons($phoneNumberId, $to, $body, $buttons, $header, $footer, $replyTo)
        );
    }

    public function sendList(
        string $phoneNumberId,
        string $to,
        string $body,
        string $button,
        array $sections,
        ?string $header = null,
        ?string $footer = null,
        ?string $replyTo = null
    ): array {
        return $this->send(
            Payloads::interactiveList($phoneNumberId, $to, $body, $button, $sections, $header, $footer, $replyTo)
        );
    }

    public function sendCtaUrl(
        string $phoneNumberId,
        string $to,
        string $body,
        string $displayText,
        string $url,
        ?string $header = null,
        ?string $footer = null,
        ?string $replyTo = null
    ): array {
        return $this->send(
            Payloads::interactiveCtaUrl($phoneNumberId, $to, $body, $displayText, $url, $header, $footer, $replyTo)
        );
    }

    public function sendTemplate(
        string $phoneNumberId,
        string $to,
        string $templateId,
        mixed $body = null,
        mixed $header = null,
        mixed $buttons = null
    ): array {
        $payload = Payloads::template($phoneNumberId, $to, $templateId, $body, $header, $buttons);
        return $this->transport->request('POST', '/v1/messages/send-template', null, $payload);
    }

    public function markRead(
        string $phoneNumberId,
        string $messageId,
        bool $typing = false
    ): array {
        $payload = Payloads::readReceipt($phoneNumberId, $messageId, $typing);
        return $this->transport->request('POST', '/v1/messages/read', null, $payload);
    }

    public function list(
        ?string $phoneNumberId = null,
        ?string $status = null,
        ?string $toNumber = null,
        int $limit = 50,
        int $offset = 0
    ): mixed {
        $params = [
            'phone_number_id' => $phoneNumberId,
            'status' => $status,
            'to_number' => $toNumber,
            'limit' => $limit,
            'offset' => $offset,
        ];
        return $this->transport->request('GET', '/v1/messages', $params);
    }

    public function get(string $messageId): array
    {
        if ($messageId === '') {
            throw new InvalidRequestError('messageId is required');
        }
        return $this->transport->request('GET', '/v1/messages/' . rawurlencode($messageId));
    }
}
