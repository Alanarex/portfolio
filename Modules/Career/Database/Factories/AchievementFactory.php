<?php

declare(strict_types=1);

namespace Modules\Career\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Career\Models\Achievement;
use Modules\Career\Models\Experience;

/** @extends Factory<Achievement> */
final class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'experience_id' => Experience::factory(),
            'key' => fake()->unique()->slug(3),
            'status' => 'draft',
            'is_quantified' => false,
            'is_verified' => false,
            'source_reference' => null,
            'sort_order' => 10,
        ];
    }
}
