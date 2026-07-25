<?php

declare(strict_types=1);

namespace Modules\Career\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Career\Models\Experience;

/** @extends Factory<Experience> */
final class ExperienceFactory extends Factory
{
    protected $model = Experience::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(3),
            'organization' => fake()->company(),
            'start_year' => 2024,
            'start_month' => null,
            'end_year' => null,
            'end_month' => null,
            'is_current' => false,
            'status' => 'draft',
            'sort_order' => 10,
            'published_at' => null,
        ];
    }
}
