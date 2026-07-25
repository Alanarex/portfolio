<?php

declare(strict_types=1);

namespace Modules\Skills\Data;

final readonly class PublicSkillsData
{
    /** @param list<array<string, mixed>> $categories */
    public function __construct(public array $categories) {}

    /** @return array{categories: list<array<string, mixed>>} */
    public function toArray(): array
    {
        return ['categories' => $this->categories];
    }
}
