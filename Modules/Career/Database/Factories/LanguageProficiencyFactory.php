<?php

declare(strict_types=1);

namespace Modules\Career\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Career\Models\LanguageProficiency;

/** @extends Factory<LanguageProficiency> */
final class LanguageProficiencyFactory extends Factory
{
    protected $model = LanguageProficiency::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['key' => fake()->unique()->slug(2), 'language_code' => 'fr', 'proficiency_kind' => 'native', 'cefr_level' => null, 'status' => 'draft', 'sort_order' => 10, 'published_at' => null];
    }
}
