<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\GitHubActivity\Contracts\PublicContributionReader;
use Modules\GitHubActivity\Models\ContributionCalendar;
use Modules\Settings\Models\SiteSetting;
use Tests\TestCase;

final class ContributionGridTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.github.username' => 'Alanarex', 'services.github.token' => 'test-token', 'services.gitlab.username' => 'alanarex-gl', 'services.gitlab.url' => 'https://gitlab.com']);
    }

    public function test_sync_stores_github_and_gitlab_day_counts_only(): void
    {
        $today = now()->format('Y-m-d');
        Http::fake([
            'api.github.com/graphql' => Http::response(['data' => ['user' => ['contributionsCollection' => ['contributionCalendar' => ['weeks' => [
                ['contributionDays' => [['date' => $today, 'contributionCount' => 3], ['date' => 'not-a-date', 'contributionCount' => 9]]],
            ]]]]]]),
            'gitlab.com/users/alanarex-gl/calendar.json' => Http::response([$today => 2, 'junk' => 5]),
            'api.github.com/users/*' => Http::response([]),
        ]);

        $this->artisan('portfolio:sync-github')->assertSuccessful();

        self::assertSame([$today => 3], ContributionCalendar::query()->where('source', 'github')->sole()->days);
        self::assertSame([$today => 2], ContributionCalendar::query()->where('source', 'gitlab')->sole()->days);
        Http::assertSent(fn ($request) => $request->url() !== 'https://api.github.com/graphql' || $request->hasHeader('Authorization'));
    }

    public function test_github_falls_back_to_the_public_profile_page_without_a_token(): void
    {
        config(['services.github.token' => null]);
        $html = '<td data-ix="0" data-date="2026-11-09" id="contribution-day-component-0-0" data-level="0"></td>'
            .'<td data-ix="1" data-date="2026-11-10" id="contribution-day-component-0-1" data-level="2"></td>'
            .'<tool-tip for="contribution-day-component-0-0" class="sr-only">No contributions on November 9th.</tool-tip>'
            .'<tool-tip for="contribution-day-component-0-1" class="sr-only">7 contributions on November 10th.</tool-tip>';
        Http::fake([
            'github.com/users/Alanarex/contributions' => Http::response($html),
            'gitlab.com/*' => Http::response([]),
            'api.github.com/users/*' => Http::response([]),
        ]);

        $this->artisan('portfolio:sync-github')->assertSuccessful();

        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'graphql'));
        self::assertSame(['2026-11-09' => 0, '2026-11-10' => 7], ContributionCalendar::query()->where('source', 'github')->sole()->days);
    }

    public function test_a_failing_source_keeps_its_previous_calendar(): void
    {
        config(['services.github.token' => null]);
        $today = now()->format('Y-m-d');
        ContributionCalendar::query()->create(['source' => 'gitlab', 'username' => 'alanarex-gl', 'days' => [$today => 4], 'synced_at' => now()->subHour()]);
        Http::fake(['gitlab.com/*' => Http::response('nope', 500), 'github.com/*' => Http::response('<html></html>')]);

        $this->artisan('portfolio:sync-github');

        self::assertSame([$today => 4], ContributionCalendar::query()->where('source', 'gitlab')->sole()->days);
        self::assertSame(0, ContributionCalendar::query()->where('source', 'github')->count());
    }

    public function test_reader_merges_sources_into_a_53_week_grid_with_levels(): void
    {
        $today = now()->format('Y-m-d');
        ContributionCalendar::query()->create(['source' => 'github', 'username' => 'Alanarex', 'days' => [$today => 3, '2000-01-01' => 50], 'synced_at' => now()]);
        ContributionCalendar::query()->create(['source' => 'gitlab', 'username' => 'alanarex-gl', 'days' => [$today => 1], 'synced_at' => now()]);

        $grid = $this->app->make(PublicContributionReader::class)->grid();

        self::assertNotNull($grid);
        self::assertCount(53, $grid->weeks);
        self::assertSame(4, $grid->total, 'Days outside the window are ignored and sources are summed.');
        self::assertSame(['github' => 3, 'gitlab' => 1], $grid->totals);
        $last = collect($grid->weeks)->flatten(1)->last();
        self::assertSame([$today, 4, 4], [$last['date'], $last['count'], $last['level']]);
    }

    public function test_home_shows_the_grid_only_when_the_activity_flag_is_on(): void
    {
        $today = now()->format('Y-m-d');
        ContributionCalendar::query()->create(['source' => 'gitlab', 'username' => 'alanarex-gl', 'days' => [$today => 2], 'synced_at' => now()]);
        $settings = SiteSetting::factory()->create(['key' => 'main', 'default_locale' => 'fr']);
        $settings->featureFlags()->updateOrCreate(['key' => 'activity'], ['enabled' => false]);

        $this->get('/fr')->assertOk()->assertDontSee('contrib__grid', false);

        $settings->featureFlags()->updateOrCreate(['key' => 'activity'], ['enabled' => true]);
        $this->app['cache']->flush();

        $this->get('/fr')->assertOk()->assertSee('contrib__grid', false)->assertSee('2 contributions sur les 12 derniers mois (GitLab 2)', false);
    }
}
