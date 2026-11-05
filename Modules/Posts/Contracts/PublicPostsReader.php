<?php

declare(strict_types=1);

namespace Modules\Posts\Contracts;

use Modules\Posts\Data\PublicPostData;

interface PublicPostsReader
{
    /** @return list<PublicPostData> */
    public function latest(string $locale, int $limit = 10): array;
}
