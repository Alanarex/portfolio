<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Posts\Enums\PostStatus;
use Modules\Posts\Models\Post;
use Tests\TestCase;

final class DashboardHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_home_requires_an_administrator(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth_version' => $user->auth_version])
            ->get('/dashboard')->assertForbidden();
    }

    public function test_administrator_sees_content_counters_and_recent_activity(): void
    {
        $administrator = User::factory()->administrator()->create();
        Post::factory()->create(['status' => PostStatus::Published]);
        Post::factory()->create(['status' => PostStatus::Draft]);

        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard/Home')
                ->where('stats.posts.published', 1)
                ->where('stats.posts.draft', 1)
                ->has('stats.projects')
                ->has('activity'));
    }
}
