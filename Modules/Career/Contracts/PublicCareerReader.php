<?php

declare(strict_types=1);

namespace Modules\Career\Contracts;

use Modules\Career\Data\PublicCareerData;

interface PublicCareerReader
{
    public function forLocale(string $locale): ?PublicCareerData;
}
