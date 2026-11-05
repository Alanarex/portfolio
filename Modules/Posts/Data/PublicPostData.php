<?php

declare(strict_types=1);

namespace Modules\Posts\Data;

use DateTimeImmutable;

final readonly class PublicPostData
{
    public function __construct(
        public int $id,
        public string $body,
        public DateTimeImmutable $publishedAt,
        public ?string $linkUrl = null,
        public ?string $linkLabel = null,
    ) {}
}
