<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Keeps public contact details out of the HTML source so address harvesters do not find them.
 *
 * The payload is JSON {h: href, l: label}, base64-encoded then reversed. It is not encryption:
 * it only defeats scrapers that look for plain or base64 email patterns. resources/js/public.ts
 * decodes it when a visitor clicks the button.
 */
final class ContactObfuscator
{
    public static function encode(string $href, string $label): string
    {
        $json = json_encode(['h' => $href, 'l' => $label], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return strrev(base64_encode($json));
    }

    /** @return array{h: string, l: string}|null */
    public static function decode(string $payload): ?array
    {
        $json = base64_decode(strrev($payload), true);
        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);

        return is_array($data) && is_string($data['h'] ?? null) && is_string($data['l'] ?? null)
            ? ['h' => $data['h'], 'l' => $data['l']]
            : null;
    }
}
