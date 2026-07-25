<?php

declare(strict_types=1);

namespace Modules\Career\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Career\Models\EducationRecord;

/** @extends Factory<EducationRecord> */
final class EducationRecordFactory extends Factory
{
    protected $model = EducationRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['key' => fake()->unique()->slug(3), 'institution' => fake()->company(), 'start_year' => 2023, 'start_month' => null, 'end_year' => 2024, 'end_month' => null, 'status' => 'draft', 'sort_order' => 10, 'published_at' => null];
    }
}
