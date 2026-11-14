<?php

declare(strict_types=1);

namespace Modules\Skills\Contracts;

interface SkillCatalog
{
    /**
     * Every skill known to the CMS (any publication state) for administrator pickers.
     *
     * @return list<array{key: string, name: string, category: string}>
     */
    public function options(): array;
}
