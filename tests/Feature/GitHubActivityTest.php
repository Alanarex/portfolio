<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\GitHubActivity\Models\GitHubSnapshot;
use Modules\Settings\Models\SiteSetting;
use Tests\TestCase;

final class GitHubActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.github.username' => 'Alanarex', 'services.github.token' => null]);
    }

    public function test_sync_keeps_only_public_owned_repositories_and_known_event_types(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'api.github.com/users/Alanarex/repos*' => Http::response([
                $this->repository('portfolio', description: 'Laravel portfolio'),
                $this->repository('secret-project', private: true),
                $this->repository('forked-lib', fork: true),
                $this->repository('old-thing', archived: true),
            ]),
            'api.github.com/users/Alanarex/events/public*' => Http::response([
                ['type' => 'PushEvent', 'public' => true, 'repo' => ['name' => 'Alanarex/portfolio'], 'payload' => ['size' => 3], 'created_at' => '2026-11-05T10:00:00Z'],
                ['type' => 'PullRequestEvent', 'public' => true, 'repo' => ['name' => 'Alanarex/portfolio'], 'payload' => ['action' => 'closed', 'pull_request' => ['merged' => true, 'title' => 'Add posts', 'html_url' => 'https://github.com/Alanarex/portfolio/pull/37']], 'created_at' => '2026-11-05T09:00:00Z'],
                ['type' => 'PullRequestEvent', 'public' => true, 'repo' => ['name' => 'Alanarex/portfolio'], 'payload' => ['action' => 'closed', 'pull_request' => ['merged' => false]], 'created_at' => '2026-11-05T08:00:00Z'],
                ['type' => 'WatchEvent', 'public' => true, 'repo' => ['name' => 'someone/else'], 'payload' => [], 'created_at' => '2026-11-05T07:00:00Z'],
                ['type' => 'PushEvent', 'public' => false, 'repo' => ['name' => 'Alanarex/private'], 'payload' => ['size' => 1], 'created_at' => '2026-11-05T06:00:00Z'],
                ['type' => 'ReleaseEvent', 'public' => true, 'repo' => ['name' => 'Alanarex/portfolio'], 'payload' => ['action' => 'published', 'release' => ['tag_name' => 'v3.0.0', 'html_url' => 'javascript:alert(1)']], 'created_at' => '2026-11-04T10:00:00Z'],
            ]),
        ]);

        $this->artisan('portfolio:sync-github')->assertSuccessful();

        $snapshot = GitHubSnapshot::query()->sole();
        self::assertSame(['portfolio'], array_column($snapshot->repositories, 'name'));
        self::assertSame(['push', 'pr_merged', 'release'], array_column($snapshot->events, 'type'));
        self::assertSame(3, $snapshot->events[0]['count']);
        self::assertSame('https://github.com/Alanarex/portfolio', $snapshot->events[2]['url'], 'Non-GitHub URLs fall back to the repository.');
        self::assertStringNotContainsString('secret-project', json_encode($snapshot->getAttributes(), JSON_THROW_ON_ERROR));
        self::assertStringNotContainsString('Alanarex/private', json_encode($snapshot->getAttributes(), JSON_THROW_ON_ERROR));

        Http::assertSent(fn ($request) => $request->hasHeader('X-GitHub-Api-Version') && ! $request->hasHeader('Authorization'));
    }

    public function test_failed_sync_keeps_the_previous_snapshot(): void
    {
        GitHubSnapshot::query()->create([
            'username' => 'Alanarex',
            'repositories' => [['name' => 'portfolio', 'url' => 'https://github.com/Alanarex/portfolio', 'description' => null, 'language' => 'PHP', 'stars' => 0, 'pushed_at' => null]],
            'events' => [],
            'synced_at' => now()->subHour(),
        ]);
        Http::fake(['api.github.com/*' => Http::response(['message' => 'API rate limit exceeded'], 403)]);

        $this->artisan('portfolio:sync-github')->assertFailed();

        self::assertSame('portfolio', GitHubSnapshot::query()->sole()->repositories[0]['name']);
    }

    public function test_sync_is_scheduled_hourly(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('portfolio:sync-github')->assertSuccessful();
    }

    public function test_home_shows_the_snapshot_only_when_the_activity_flag_is_on(): void
    {
        GitHubSnapshot::query()->create([
            'username' => 'Alanarex',
            'repositories' => [['name' => 'portfolio', 'url' => 'https://github.com/Alanarex/portfolio', 'description' => 'Laravel <b>portfolio</b>', 'language' => 'PHP', 'stars' => 2, 'pushed_at' => '2026-11-05T10:00:00Z']],
            'events' => [['type' => 'push', 'repo' => 'Alanarex/portfolio', 'url' => 'https://github.com/Alanarex/portfolio', 'title' => null, 'count' => 3, 'at' => '2026-11-05T10:00:00Z']],
            'synced_at' => now(),
        ]);

        $settings = SiteSetting::factory()->create(['key' => 'main', 'default_locale' => 'fr']);
        $settings->featureFlags()->updateOrCreate(['key' => 'activity'], ['enabled' => false]);

        $this->get('/fr')->assertOk()->assertDontSee('Dépôts récents');

        $settings->featureFlags()->updateOrCreate(['key' => 'activity'], ['enabled' => true]);
        $this->flushPublicCaches();

        $this->get('/fr')
            ->assertOk()
            ->assertSee('Dépôts récents')
            ->assertSee('3 commits sur Alanarex/portfolio')
            ->assertSee('Laravel &lt;b&gt;portfolio&lt;/b&gt;', false)
            ->assertSee('https://github.com/Alanarex/portfolio', false);
    }

    private function flushPublicCaches(): void
    {
        $this->app['cache']->flush();
    }

    /** @return array<string, mixed> */
    private function repository(string $name, bool $private = false, bool $fork = false, bool $archived = false, ?string $description = null): array
    {
        return [
            'name' => $name,
            'html_url' => "https://github.com/Alanarex/{$name}",
            'private' => $private,
            'fork' => $fork,
            'archived' => $archived,
            'description' => $description,
            'language' => 'PHP',
            'stargazers_count' => 1,
            'pushed_at' => '2026-11-05T10:00:00Z',
        ];
    }
}
