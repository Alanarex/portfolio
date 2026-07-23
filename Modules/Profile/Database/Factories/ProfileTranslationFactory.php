<?php

declare(strict_types=1);

namespace Modules\Profile\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Profile\Models\Profile;
use Modules\Profile\Models\ProfileTranslation;

/** @extends Factory<ProfileTranslation> */
final class ProfileTranslationFactory extends Factory
{
    protected $model = ProfileTranslation::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'locale' => 'fr',
            'professional_titles' => [fake()->jobTitle()],
            'summary' => fake()->sentence(),
            'location_label' => fake()->city(),
            'availability' => fake()->sentence(4),
            'biography' => fake()->paragraph(),
        ];
    }
}
