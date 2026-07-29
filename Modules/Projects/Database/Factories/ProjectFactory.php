<?php

declare(strict_types=1);

namespace Modules\Projects\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Projects\Models\Project;

/** @extends Factory<Project> */
final class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'slug' => fake()->unique()->slug(3),
            'lifecycle_status' => 'completed',
            'technologies' => ['Laravel', 'PHP'],
            'start_year' => 2025,
            'start_month' => null,
            'end_year' => 2026,
            'end_month' => null,
            'is_ongoing' => false,
            'repository_visibility' => 'none',
            'repository_url' => null,
            'show_repository' => false,
            'demo_url' => null,
            'show_demo' => false,
            'publication_status' => 'draft',
            'is_featured' => false,
            'featured_order' => null,
            'sort_order' => 10,
            'source_reference' => null,
            'published_at' => null,
        ];
    }
}
