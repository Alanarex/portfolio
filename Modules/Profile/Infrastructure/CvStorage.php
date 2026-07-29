<?php

declare(strict_types=1);

namespace Modules\Profile\Infrastructure;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

final class CvStorage
{
    /**
     * @return array{
     *   disk: string,
     *   path: string,
     *   original_filename: string,
     *   mime_type: string,
     *   size_bytes: int,
     *   checksum_sha256: string
     * }
     */
    public function store(UploadedFile $document): array
    {
        $disk = $this->privateDisk();
        $path = 'documents/'.Str::uuid().'.pdf';
        $temporaryPath = $document->getRealPath();
        $checksum = hash_file('sha256', $temporaryPath);
        $size = $document->getSize();

        if (! is_string($checksum) || ! is_int($size)) {
            throw new RuntimeException('Unable to inspect the CV upload.');
        }

        $stored = Storage::disk($disk)->putFileAs('documents', $document, basename($path));
        if ($stored !== $path) {
            throw new RuntimeException('Unable to store the CV upload.');
        }

        return [
            'disk' => $disk,
            'path' => $path,
            'original_filename' => $document->getClientOriginalName(),
            'mime_type' => 'application/pdf',
            'size_bytes' => $size,
            'checksum_sha256' => $checksum,
        ];
    }

    public function delete(?string $disk, ?string $path): void
    {
        if ($disk !== null && $path !== null) {
            Storage::disk($disk)->delete($path);
        }
    }

    public function isIntact(?string $disk, ?string $path, ?string $checksum): bool
    {
        if ($disk === null || $path === null || $checksum === null || ! Storage::disk($disk)->exists($path)) {
            return false;
        }

        $contents = Storage::disk($disk)->get($path);

        return hash_equals($checksum, hash('sha256', $contents));
    }

    private function privateDisk(): string
    {
        $disk = (string) config('profile.cv_disk', 'cv');
        $configuration = config("filesystems.disks.{$disk}");

        if (! is_array($configuration)
            || ($configuration['visibility'] ?? null) !== 'private'
            || ($configuration['serve'] ?? false) === true
            || isset($configuration['url'])) {
            throw new RuntimeException('The CV filesystem disk must be explicitly private and non-public.');
        }

        return $disk;
    }
}
