<?php

declare(strict_types=1);

namespace Modules\Projects\Infrastructure;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Projects\Contracts\MediaStorage;
use Modules\Projects\Data\StoredMediaData;
use Modules\Projects\Enums\MediaKind;
use RuntimeException;

final class LocalMediaStorage implements MediaStorage
{
    public function store(
        UploadedFile $file,
        string $ownerUuid,
        MediaKind $kind,
        string $mediaUuid,
    ): StoredMediaData {
        $realPath = $file->getRealPath();
        if ($realPath === false || ! is_file($realPath)) {
            throw ValidationException::withMessages(['file' => 'Le fichier téléversé est illisible.']);
        }

        $mimeType = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($realPath);
        $extension = mb_strtolower($file->getClientOriginalExtension());
        $policy = MediaUploadPolicy::for($kind);
        $allowedExtensions = $policy['mimes'][$mimeType] ?? [];
        $size = $file->getSize();

        if (! in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages(['file' => 'Le type MIME et l’extension du fichier ne correspondent pas au format autorisé.']);
        }
        if ($size === false || $size < 1 || $size > $policy['max_bytes']) {
            throw ValidationException::withMessages(['file' => 'La taille du fichier dépasse la limite autorisée.']);
        }

        $width = null;
        $height = null;
        if ($policy['image']) {
            $dimensions = @getimagesize($realPath);
            if ($dimensions === false) {
                throw ValidationException::withMessages(['file' => 'L’image ne peut pas être décodée de manière sûre.']);
            }
            [$width, $height] = $dimensions;
            if ($width < 1 || $height < 1 || $width > 6000 || $height > 6000 || ($width * $height) > 10_000_000) {
                throw ValidationException::withMessages(['file' => 'Les dimensions de l’image dépassent la limite autorisée.']);
            }

            $contents = file_get_contents($realPath);
            if ($contents === false || ! function_exists('imagecreatefromstring')) {
                throw new RuntimeException('Secure image decoding requires the GD extension.');
            }
            $decoded = @imagecreatefromstring($contents);
            if ($decoded === false) {
                throw ValidationException::withMessages(['file' => 'L’image est tronquée ou ne peut pas être décodée intégralement.']);
            }
            try {
                $decodedWidth = imagesx($decoded);
                $decodedHeight = imagesy($decoded);
                if ($decodedWidth !== $width || $decodedHeight !== $height) {
                    throw ValidationException::withMessages(['file' => 'Les dimensions décodées de l’image sont incohérentes.']);
                }
            } finally {
                imagedestroy($decoded);
            }
        }

        $canonicalExtension = MediaUploadPolicy::canonicalExtension($mimeType);
        if ($canonicalExtension === null) {
            throw ValidationException::withMessages(['file' => 'Ce format de fichier n’est pas autorisé.']);
        }

        $disk = (string) config('projects.media_disk', 'local');
        if ($disk === '' || $disk === 'public') {
            throw new RuntimeException('Project media must use a private filesystem disk.');
        }
        $directory = "projects/{$ownerUuid}/{$kind->value}";
        $storedPath = Storage::disk($disk)->putFileAs(
            $directory,
            $file,
            "{$mediaUuid}.{$canonicalExtension}",
            ['visibility' => 'private'],
        );
        if ($storedPath === false) {
            throw new RuntimeException('Unable to store the uploaded media.');
        }

        $originalFilename = preg_replace('/[^\pL\pN._ -]/u', '_', basename($file->getClientOriginalName()));
        $checksum = hash_file('sha256', $realPath);
        if ($checksum === false) {
            $this->delete($disk, $storedPath);
            throw new RuntimeException('Unable to checksum the uploaded media.');
        }

        return new StoredMediaData(
            disk: $disk,
            path: $storedPath,
            originalFilename: filled($originalFilename) ? (string) $originalFilename : "upload.{$canonicalExtension}",
            mimeType: $mimeType,
            extension: $canonicalExtension,
            sizeBytes: (int) $size,
            checksumSha256: $checksum,
            width: $width,
            height: $height,
        );
    }

    public function delete(string $disk, string $path): void
    {
        $filesystem = Storage::disk($disk);
        if ($filesystem->exists($path) && ! $filesystem->delete($path)) {
            throw new RuntimeException('Unable to delete the stored media.');
        }
    }
}
