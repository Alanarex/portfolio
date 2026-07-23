<?php

declare(strict_types=1);

namespace Modules\Settings\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Settings\Models\PublicFeatureFlag;
use Modules\Settings\Models\SiteSetting;

/** @extends Factory<PublicFeatureFlag> */
final class PublicFeatureFlagFactory extends Factory
{
    protected $model = PublicFeatureFlag::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'site_setting_id' => SiteSetting::factory(),
            'key' => fake()->unique()->randomElement(['projects', 'contact', 'cv', 'three_d']),
            'enabled' => false,
        ];
    }
}
