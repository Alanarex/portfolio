<?php

declare(strict_types=1);

namespace Modules\Projects\Contracts;

use Illuminate\Http\UploadedFile;
use Modules\Projects\Data\StoredMediaData;
use Modules\Projects\Enums\MediaKind;

interface MediaStorage
{
    public function store(
        UploadedFile $file,
        string $ownerUuid,
        MediaKind $kind,
        string $mediaUuid,
    ): StoredMediaData;

    public function delete(string $disk, string $path): void;
}
