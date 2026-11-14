<?php

declare(strict_types=1);

namespace Modules\Skills\Infrastructure;

use Modules\Skills\Contracts\SkillCatalog;
use Modules\Skills\Models\Skill;

final class DatabaseSkillCatalog implements SkillCatalog
{
    public function options(): array
    {
        $options = [];
        $skills = Skill::query()->with(['translations', 'category.translations'])->orderBy('sort_order')->orderBy('key')->get();
        foreach ($skills as $skill) {
            $name = $skill->translations->where('locale', 'fr')->pluck('name')->first() ?? $skill->key;
            $category = $skill->category->translations->where('locale', 'fr')->pluck('name')->first() ?? $skill->category->key;
            $options[] = ['key' => $skill->key, 'name' => $name, 'category' => $category];
        }
        usort($options, fn (array $a, array $b): int => [$a['category'], $a['name']] <=> [$b['category'], $b['name']]);

        return $options;
    }
}
