<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Posts\Models\Post;
use Tests\TestCase;

final class PostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_dashboard_requires_an_administrator(): void
    {
        $this->get('/dashboard/posts')->assertRedirect('/login');
        $this->post('/dashboard/posts', [])->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth_version' => $user->auth_version])
            ->get('/dashboard/posts')->assertForbidden();
        $this->actingAs($user)->withSession(['auth_version' => $user->auth_version])
            ->post('/dashboard/posts', $this->payload())->assertForbidden();

        self::assertSame(0, Post::query()->count());
    }

    public function test_administrator_creates_updates_and_deletes_a_post(): void
    {
        $administrator = User::factory()->administrator()->create();
        $as = fn () => $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version]);

        $as()->post('/dashboard/posts', $this->payload())->assertRedirect();
        $post = Post::query()->sole();
        self::assertSame('published', $post->status->value);
        self::assertNotNull($post->published_at);
        $firstPublication = $post->published_at;

        $as()->get('/dashboard/posts')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/Posts/Index')->has('posts', 1)
                ->where('posts.0.body', 'Nouvelle version du portfolio en ligne.'));

        $this->travel(1)->hours();
        $as()->put("/dashboard/posts/{$post->id}", $this->payload(['body' => 'Texte corrigé.']))->assertRedirect();
        $post->refresh();
        self::assertSame('Texte corrigé.', $post->body);
        self::assertTrue($firstPublication->equalTo($post->published_at), 'Editing keeps the first publication date.');

        $as()->put("/dashboard/posts/{$post->id}", $this->payload(['status' => 'draft']))->assertRedirect();
        self::assertNull($post->refresh()->published_at);

        $as()->delete("/dashboard/posts/{$post->id}")->assertRedirect();
        self::assertSame(0, Post::query()->count());
    }

    public function test_post_validation_rejects_bad_input(): void
    {
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version])
            ->post('/dashboard/posts', $this->payload([
                'body' => '',
                'locale' => 'de',
                'status' => 'archived',
                'link_url' => 'javascript:alert(1)',
            ]))
            ->assertSessionHasErrors(['body', 'locale', 'status', 'link_url']);

        self::assertSame(0, Post::query()->count());
    }

    public function test_home_shows_only_published_posts_of_the_current_locale_as_plain_text(): void
    {
        Post::factory()->published()->create(['body' => "Première ligne\n<script>alert(1)</script>"]);
        Post::factory()->create(['body' => 'Brouillon caché']);
        Post::factory()->published()->create(['locale' => 'en', 'body' => 'English only update']);
        Post::factory()->create(['status' => 'published', 'published_at' => now()->addDay(), 'body' => 'Publication future']);

        $this->get('/fr')
            ->assertOk()
            ->assertSee('Publications')
            ->assertSee('Première ligne<br>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('Brouillon caché')
            ->assertDontSee('English only update')
            ->assertDontSee('Publication future');

        $this->get('/en')->assertOk()->assertSee('English only update');
    }

    public function test_home_hides_the_posts_section_when_there_is_nothing_to_show(): void
    {
        $this->get('/fr')->assertOk()->assertDontSee('id="posts"', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'locale' => 'fr',
            'body' => 'Nouvelle version du portfolio en ligne.',
            'link_url' => 'https://github.com/Alanarex/portfolio',
            'link_label' => 'Voir le code',
            'status' => 'published',
            ...$overrides,
        ];
    }
}
