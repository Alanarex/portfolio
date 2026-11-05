<?php

declare(strict_types=1);

namespace Modules\Posts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Posts\Models\Post;

/** @extends Factory<Post> */
final class PostFactory extends Factory
{
    protected $model = Post::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'locale' => 'fr',
            'body' => fake()->sentence(12),
            'link_url' => null,
            'link_label' => null,
            'status' => 'draft',
            'published_at' => null,
        ];
    }

    public function published(): self
    {
        return $this->state(fn (): array => ['status' => 'published', 'published_at' => now()->subHour()]);
    }
}
