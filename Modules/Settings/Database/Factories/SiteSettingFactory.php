<?php

declare(strict_types=1);

namespace Modules\Settings\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Settings\Models\SiteSetting;

/** @extends Factory<SiteSetting> */
final class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'site_name' => fake()->words(2, true),
            'default_locale' => 'fr',
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => fake()->e164PhoneNumber(),
            'show_email' => false,
            'show_phone' => false,
        ];
    }
}
