<?php

namespace Zecto;

use Zecto\Exceptions\InvalidRequestError;

class Payloads
{
    private static function digitsOnly(?string $value): string
    {
        return preg_replace('/\D/', '', $value ?? '') ?? '';
    }

    private static function fillWaIds(array $contactsList): array
    {
        foreach ($contactsList as &$contact) {
            if (!isset($contact['phones']) || !is_array($contact['phones'])) {
                continue;
            }
            foreach ($contact['phones'] as &$phone) {
                if (empty($phone['wa_id']) && !empty($phone['phone'])) {
                    $phone['wa_id'] = self::digitsOnly($phone['phone']);
                }
            }
            unset($phone);
        }
        unset($contact);
        return $contactsList;
    }

    private static function base(
        string $phoneNumberId,
        string $to,
        string $msgType,
        ?string $replyTo = null
    ): array {
        $payload = [
            'phone_number_id' => $phoneNumberId,
            'to' => $to,
            'type' => $msgType,
        ];
        if ($replyTo !== null && $replyTo !== '') {
            $payload['context'] = ['message_id' => $replyTo];
        }
        return $payload;
    }

    private static function mediaObject(
        ?string $link,
        ?string $mediaId,
        ?string $caption = null,
        ?string $filename = null
    ): array {
        if (($link === null || $link === '') && ($mediaId === null || $mediaId === '')) {
            throw new InvalidRequestError("Provide either 'link' or 'mediaId'");
        }
        $obj = [];
        if ($link !== null && $link !== '') {
            $obj['link'] = $link;
        }
        if ($mediaId !== null && $mediaId !== '') {
            $obj['id'] = $mediaId;
        }
        if ($caption !== null) {
            $obj['caption'] = $caption;
        }
        if ($filename !== null) {
            $obj['filename'] = $filename;
        }
        return $obj;
    }

    public static function text(
        string $phoneNumberId,
        string $to,
        string $body,
        bool $previewUrl = false,
        ?string $replyTo = null
    ): array {
        $payload = self::base($phoneNumberId, $to, 'text', $replyTo);
        $payload['text'] = ['body' => $body, 'preview_url' => $previewUrl];
        return $payload;
    }

    public static function image(
        string $phoneNumberId,
        string $to,
        ?string $link = null,
        ?string $mediaId = null,
        ?string $caption = null,
        ?string $replyTo = null
    ): array {
        $payload = self::base($phoneNumberId, $to, 'image', $replyTo);
        $payload['image'] = self::mediaObject($link, $mediaId, $caption);
        return $payload;
    }

    public static function video(
        string $phoneNumberId,
        string $to,
        ?string $link = null,
        ?string $mediaId = null,
        ?string $caption = null,
        ?string $replyTo = null
    ): array {
        $payload = self::base($phoneNumberId, $to, 'video', $replyTo);
        $payload['video'] = self::mediaObject($link, $mediaId, $caption);
        return $payload;
    }

    public static function audio(
        string $phoneNumberId,
        string $to,
        ?string $link = null,
        ?string $mediaId = null,
        ?string $replyTo = null
    ): array {
        $payload = self::base($phoneNumberId, $to, 'audio', $replyTo);
        $payload['audio'] = self::mediaObject($link, $mediaId);
        return $payload;
    }

    public static function document(
        string $phoneNumberId,
        string $to,
        ?string $link = null,
        ?string $mediaId = null,
        ?string $caption = null,
        ?string $filename = null,
        ?string $replyTo = null
    ): array {
        $payload = self::base($phoneNumberId, $to, 'document', $replyTo);
        $payload['document'] = self::mediaObject($link, $mediaId, $caption, $filename);
        return $payload;
    }

    public static function location(
        string $phoneNumberId,
        string $to,
        float $latitude,
        float $longitude,
        ?string $name = null,
        ?string $address = null,
        ?string $replyTo = null
    ): array {
        $payload = self::base($phoneNumberId, $to, 'location', $replyTo);
        $loc = ['latitude' => $latitude, 'longitude' => $longitude];
        if ($name !== null) {
            $loc['name'] = $name;
        }
        if ($address !== null) {
            $loc['address'] = $address;
        }
        $payload['location'] = $loc;
        return $payload;
    }

    public static function contacts(
        string $phoneNumberId,
        string $to,
        array $contactsList,
        ?string $replyTo = null
    ): array {
        $payload = self::base($phoneNumberId, $to, 'contacts', $replyTo);
        $payload['contacts'] = self::fillWaIds($contactsList);
        return $payload;
    }

    public static function interactive(
        string $phoneNumberId,
        string $to,
        array $interactiveObj,
        ?string $replyTo = null
    ): array {
        $payload = self::base($phoneNumberId, $to, 'interactive', $replyTo);
        $payload['interactive'] = $interactiveObj;
        return $payload;
    }

    private static function interactiveShell(
        string $itype,
        string $body,
        ?string $header = null,
        ?string $footer = null
    ): array {
        $obj = ['type' => $itype, 'body' => ['text' => $body]];
        if ($header !== null) {
            $obj['header'] = ['type' => 'text', 'text' => $header];
        }
        if ($footer !== null) {
            $obj['footer'] = ['text' => $footer];
        }
        return $obj;
    }

    private static function slugifyId(?string $title, int $index): string
    {
        $slug = strtolower($title ?? '');
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug) ?? '';
        $slug = trim($slug, '_');
        return $slug !== '' ? $slug : "option_{$index}";
    }

    public static function interactiveButtons(
        string $phoneNumberId,
        string $to,
        string $body,
        array $buttons,
        ?string $header = null,
        ?string $footer = null,
        ?string $replyTo = null
    ): array {
        if (count($buttons) === 0) {
            throw new InvalidRequestError('At least one button is required');
        }
        if (count($buttons) > 3) {
            throw new InvalidRequestError('A maximum of 3 reply buttons is allowed');
        }

        $actionButtons = [];
        foreach ($buttons as $i => $b) {
            if (is_string($b)) {
                $bid = self::slugifyId($b, $i);
                $title = $b;
            } else {
                $title = $b['title'] ?? null;
                $bid = $b['id'] ?? self::slugifyId($title, $i);
            }
            if ($title === null || $title === '') {
                $n = $i + 1;
                throw new InvalidRequestError("Button {$n}: title is required");
            }
            $actionButtons[] = ['type' => 'reply', 'reply' => ['id' => $bid, 'title' => $title]];
        }

        $obj = self::interactiveShell('button', $body, $header, $footer);
        $obj['action'] = ['buttons' => $actionButtons];
        return self::interactive($phoneNumberId, $to, $obj, $replyTo);
    }

    public static function interactiveList(
        string $phoneNumberId,
        string $to,
        string $body,
        string $button,
        array $sections,
        ?string $header = null,
        ?string $footer = null,
        ?string $replyTo = null
    ): array {
        if ($button === '') {
            throw new InvalidRequestError("A menu 'button' label is required");
        }
        if (count($sections) === 0) {
            throw new InvalidRequestError('At least one section is required');
        }

        $normSections = [];
        foreach ($sections as $si => $section) {
            $rows = $section['rows'] ?? [];
            if (count($rows) === 0) {
                $n = $si + 1;
                throw new InvalidRequestError("Section {$n}: at least one row is required");
            }
            $normRows = [];
            foreach ($rows as $ri => $row) {
                if (is_string($row)) {
                    $rid = self::slugifyId($row, $ri);
                    $title = $row;
                    $desc = null;
                } else {
                    $title = $row['title'] ?? null;
                    $rid = $row['id'] ?? self::slugifyId($title, $ri);
                    $desc = $row['description'] ?? null;
                }
                if ($title === null || $title === '') {
                    $sn = $si + 1;
                    $rn = $ri + 1;
                    throw new InvalidRequestError("Section {$sn} row {$rn}: title is required");
                }
                $nr = ['id' => $rid, 'title' => $title];
                if ($desc !== null) {
                    $nr['description'] = $desc;
                }
                $normRows[] = $nr;
            }
            $ns = ['rows' => $normRows];
            if (isset($section['title'])) {
                $ns['title'] = $section['title'];
            }
            $normSections[] = $ns;
        }

        $obj = self::interactiveShell('list', $body, $header, $footer);
        $obj['action'] = ['button' => $button, 'sections' => $normSections];
        return self::interactive($phoneNumberId, $to, $obj, $replyTo);
    }

    public static function interactiveCtaUrl(
        string $phoneNumberId,
        string $to,
        string $body,
        string $displayText,
        string $url,
        ?string $header = null,
        ?string $footer = null,
        ?string $replyTo = null
    ): array {
        if ($displayText === '') {
            throw new InvalidRequestError("'displayText' is required");
        }
        if ($url === '') {
            throw new InvalidRequestError("'url' is required");
        }

        $obj = self::interactiveShell('cta_url', $body, $header, $footer);
        $obj['action'] = [
            'name' => 'cta_url',
            'parameters' => ['display_text' => $displayText, 'url' => $url],
        ];
        return self::interactive($phoneNumberId, $to, $obj, $replyTo);
    }

    public static function template(
        string $phoneNumberId,
        string $to,
        string $templateId,
        mixed $body = null,
        mixed $header = null,
        mixed $buttons = null
    ): array {
        $payload = [
            'phone_number_id' => $phoneNumberId,
            'to' => $to,
            'template_id' => $templateId,
        ];
        if ($body !== null) {
            $payload['body'] = is_array($body) ? $body : [$body];
        }
        if ($header !== null) {
            $payload['header'] = $header;
        }
        if ($buttons !== null) {
            $payload['buttons'] = is_array($buttons) ? $buttons : [$buttons];
        }
        return $payload;
    }

    public static function readReceipt(
        string $phoneNumberId,
        string $messageId,
        bool $typing = false
    ): array {
        return [
            'phone_number_id' => $phoneNumberId,
            'message_id' => $messageId,
            'typing' => $typing,
        ];
    }
}
