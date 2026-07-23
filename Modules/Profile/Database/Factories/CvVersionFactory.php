<?php

declare(strict_types=1);

namespace Modules\Profile\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Profile\Models\CvVersion;
use Modules\Profile\Models\Profile;

/** @extends Factory<CvVersion> */
final class CvVersionFactory extends Factory
{
    protected $model = CvVersion::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'locale' => 'fr',
            'label' => 'CV français',
            'version_label' => fake()->unique()->numerify('v1.##'),
            'is_verified' => false,
            'published_at' => null,
            'archived_at' => null,
        ];
    }
}
