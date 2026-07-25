<?php

declare(strict_types=1);

namespace Modules\Career\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Career\Models\Certification;

/** @extends Factory<Certification> */
final class CertificationFactory extends Factory
{
    protected $model = Certification::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['key' => fake()->unique()->slug(3), 'issuer' => null, 'credential_id' => null, 'verification_url' => null, 'issue_year' => 2026, 'issue_month' => null, 'result' => null, 'is_verified' => false, 'source_reference' => null, 'status' => 'draft', 'sort_order' => 10, 'published_at' => null];
    }
}
