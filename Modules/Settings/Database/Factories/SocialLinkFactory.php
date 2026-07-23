<?php

declare(strict_types=1);

namespace Modules\Settings\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Settings\Models\SiteSetting;
use Modules\Settings\Models\SocialLink;

/** @extends Factory<SocialLink> */
final class SocialLinkFactory extends Factory
{
    protected $model = SocialLink::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'site_setting_id' => SiteSetting::factory(),
            'platform' => fake()->unique()->randomElement(['github', 'gitlab', 'linkedin', 'whatsapp', 'scheduling']),
            'url' => fake()->url(),
            'is_enabled' => false,
            'is_public' => false,
            'sort_order' => 0,
        ];
    }
}
