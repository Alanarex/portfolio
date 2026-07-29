<?php

declare(strict_types=1);

namespace Modules\Projects\Contracts;

use Modules\Projects\Data\PublicProjectData;

interface PublicProjectReader
{
    /** @return list<PublicProjectData> */
    public function all(string $locale): array;

    /** @return list<PublicProjectData> */
    public function featured(string $locale): array;

    public function findBySlug(string $locale, string $slug): ?PublicProjectData;
}
