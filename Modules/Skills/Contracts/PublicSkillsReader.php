<?php

declare(strict_types=1);

namespace Modules\Skills\Contracts;

use Modules\Skills\Data\PublicSkillsData;

interface PublicSkillsReader
{
    public function forLocale(string $locale): ?PublicSkillsData;
}
