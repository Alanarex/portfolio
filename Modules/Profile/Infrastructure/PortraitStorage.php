<?php

declare(strict_types=1);

namespace Modules\Profile\Infrastructure;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stockage de la photo de profil. Le fichier reste sur un disque privé et n'est servi que par la route publique dédiée.
 * Quand GD est disponible, l'image est réencodée en WebP (960 px de large au plus), ce qui retire aussi ses métadonnées.
 */
final class PortraitStorage
{
    private const MAX_WIDTH = 960;

    public const MIME_TYPES = ['webp' => 'image/webp', 'jpg' => 'image/jpeg', 'png' => 'image/png'];

    /** @return array{path: string, checksum: string} */
    public function store(UploadedFile $image): array
    {
        $contents = file_get_contents($image->getRealPath());
        if ($contents === false || @getimagesizefromstring($contents) === false) {
            throw new RuntimeException('Unable to read the portrait upload.');
        }

        $extension = match ($image->getMimeType()) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        $encoded = $this->encode($contents);
        if ($encoded !== null) {
            $contents = $encoded;
            $extension = 'webp';
        }

        $path = Str::uuid().'.'.$extension;
        if (! Storage::disk($this->disk())->put($path, $contents)) {
            throw new RuntimeException('Unable to store the portrait upload.');
        }

        return ['path' => $path, 'checksum' => hash('sha256', $contents)];
    }

    public function delete(?string $path): void
    {
        if ($path !== null) {
            Storage::disk($this->disk())->delete($path);
        }
    }

    public function disk(): string
    {
        return (string) config('profile.portrait_disk', 'portrait');
    }

    private function encode(string $contents): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return null;
        }

        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        if ($width > self::MAX_WIDTH) {
            $scaled = imagescale($source, self::MAX_WIDTH);
            if ($scaled !== false) {
                $source = $scaled;
            }
        }
        imagepalettetotruecolor($source);

        ob_start();
        $written = imagewebp($source, null, 84);
        $output = ob_get_clean();

        return $written && $output !== false && $output !== '' ? $output : null;
    }
}
