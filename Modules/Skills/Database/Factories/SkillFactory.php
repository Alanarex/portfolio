<?php

declare(strict_types=1);

namespace Modules\Skills\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Skills\Models\Skill;
use Modules\Skills\Models\SkillCategory;

/** @extends Factory<Skill> */
final class SkillFactory extends Factory
{
    protected $model = Skill::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['skill_category_id' => SkillCategory::factory(), 'key' => fake()->unique()->slug(2), 'status' => 'draft', 'is_visible' => false, 'sort_order' => 10, 'published_at' => null];
    }
}
