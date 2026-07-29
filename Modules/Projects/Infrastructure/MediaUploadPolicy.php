<?php

declare(strict_types=1);

namespace Modules\Projects\Infrastructure;

use Modules\Projects\Enums\MediaKind;

final class MediaUploadPolicy
{
    /**
     * @return array{mimes: array<string, list<string>>, max_bytes: int, image: bool}
     */
    public static function for(MediaKind $kind): array
    {
        if (in_array($kind, [MediaKind::Image, MediaKind::Screenshot, MediaKind::Poster], true)) {
            return [
                'mimes' => [
                    'image/jpeg' => ['jpg', 'jpeg'],
                    'image/png' => ['png'],
                    'image/webp' => ['webp'],
                ],
                'max_bytes' => 8 * 1024 * 1024,
                'image' => true,
            ];
        }

        return [
            'mimes' => ['application/pdf' => ['pdf']],
            'max_bytes' => 20 * 1024 * 1024,
            'image' => false,
        ];
    }

    public static function canonicalExtension(string $mimeType): ?string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            default => null,
        };
    }
}
