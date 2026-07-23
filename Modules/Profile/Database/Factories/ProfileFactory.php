<?php

declare(strict_types=1);

namespace Modules\Profile\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Profile\Models\Profile;

/** @extends Factory<Profile> */
final class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'display_name' => fake()->name(),
            'is_published' => false,
            'show_location' => false,
            'show_availability' => false,
            'published_at' => null,
        ];
    }
}
