<?php

declare(strict_types=1);

namespace Modules\Projects\Data;

final readonly class StoredMediaData
{
    public function __construct(
        public string $disk,
        public string $path,
        public string $originalFilename,
        public string $mimeType,
        public string $extension,
        public int $sizeBytes,
        public string $checksumSha256,
        public ?int $width,
        public ?int $height,
    ) {}
}
