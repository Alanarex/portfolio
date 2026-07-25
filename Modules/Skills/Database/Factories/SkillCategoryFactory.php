<?php

declare(strict_types=1);

namespace Modules\Skills\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Skills\Models\SkillCategory;

/** @extends Factory<SkillCategory> */
final class SkillCategoryFactory extends Factory
{
    protected $model = SkillCategory::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['key' => fake()->unique()->slug(2), 'status' => 'draft', 'is_visible' => false, 'sort_order' => 10, 'published_at' => null];
    }
}
