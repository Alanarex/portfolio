<?php

declare(strict_types=1);

namespace Modules\Profile\Contracts;

use Modules\Profile\Data\PublicProfileData;

interface PublicProfileReader
{
    public function forLocale(string $locale): ?PublicProfileData;
}
