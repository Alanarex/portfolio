<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Application;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\Client\PendingRequest;
use Modules\GitHubActivity\Models\ContributionCalendar;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Stores per-day contribution counts from GitHub (GraphQL, read-only token) and GitLab (public calendar).
 * Only counts per day are kept. A failing source keeps its previous calendar.
 */
final class SyncContributionCalendars
{
    private const GITHUB_QUERY = 'query($login: String!) { user(login: $login) { contributionsCollection { contributionCalendar { weeks { contributionDays { date contributionCount } } } } } }';

    public function __construct(
        private readonly Http $http,
        private readonly Config $config,
        private readonly LoggerInterface $logger,
    ) {}

    /** @return array<string, bool> Result per source that is configured. */
    public function execute(): array
    {
        $results = [];

        $githubUser = (string) $this->config->get('services.github.username');
        $githubToken = $this->config->get('services.github.token');
        if (preg_match('/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,38})$/', $githubUser) === 1) {
            // With a read-only token: official GraphQL API. Without: the public contributions page of the profile.
            $results['github'] = $this->store('github', $githubUser, fn (): array => is_string($githubToken) && $githubToken !== ''
                ? $this->github($githubUser, $githubToken)
                : $this->githubPublicPage($githubUser));
        }

        $gitlabUser = (string) $this->config->get('services.gitlab.username');
        if (preg_match('/^[A-Za-z0-9_.-]{1,100}$/', $gitlabUser) === 1) {
            $results['gitlab'] = $this->store('gitlab', $gitlabUser, fn (): array => $this->gitlab($gitlabUser));
        }

        return $results;
    }

    /** @param  callable(): array<string, int>  $fetch */
    private function store(string $source, string $username, callable $fetch): bool
    {
        try {
            $days = $fetch();
        } catch (Throwable $exception) {
            $this->logger->warning('Contribution calendar sync failed; previous calendar kept.', ['source' => $source, 'exception' => $exception::class]);

            return false;
        }

        ContributionCalendar::query()->updateOrCreate(
            ['source' => $source, 'username' => $username],
            ['days' => $days, 'synced_at' => now()],
        );

        return true;
    }

    /** @return array<string, int> */
    private function github(string $login, string $token): array
    {
        $json = $this->client()
            ->withToken($token)
            ->post('https://api.github.com/graphql', ['query' => self::GITHUB_QUERY, 'variables' => ['login' => $login]])
            ->throw()
            ->json();

        $weeks = data_get($json, 'data.user.contributionsCollection.contributionCalendar.weeks');
        if (! is_array($weeks)) {
            throw new \UnexpectedValueException('Unexpected GitHub response.');
        }

        $days = [];
        foreach ($weeks as $week) {
            foreach (is_array($week) ? ($week['contributionDays'] ?? []) : [] as $day) {
                if (is_array($day)) {
                    $this->addDay($days, $day['date'] ?? null, $day['contributionCount'] ?? null);
                }
            }
        }

        return $days;
    }

    /** @return array<string, int> */
    private function githubPublicPage(string $login): array
    {
        $html = $this->client()->accept('text/html')->get('https://github.com/users/'.rawurlencode($login).'/contributions')->throw()->body();

        $dates = [];
        if (preg_match_all('/<td[^>]*data-date="(\d{4}-\d{2}-\d{2})"[^>]*id="([\w-]+)"/', $html, $cells, PREG_SET_ORDER) !== false) {
            foreach ($cells as $cell) {
                $dates[$cell[2]] = $cell[1];
            }
        }
        if ($dates === []) {
            throw new \UnexpectedValueException('Unexpected GitHub contributions page.');
        }

        $days = [];
        if (preg_match_all('/<tool-tip[^>]*\bfor="([\w-]+)"[^>]*>\s*(No|\d+) contributions? on/', $html, $tips, PREG_SET_ORDER) !== false) {
            foreach ($tips as $tip) {
                if (isset($dates[$tip[1]])) {
                    $days[$dates[$tip[1]]] = $tip[2] === 'No' ? 0 : (int) $tip[2];
                }
            }
        }

        return $days;
    }

    /** @return array<string, int> */
    private function gitlab(string $username): array
    {
        $base = rtrim((string) $this->config->get('services.gitlab.url', 'https://gitlab.com'), '/');
        if (! str_starts_with($base, 'https://')) {
            throw new \UnexpectedValueException('GitLab URL must use https.');
        }

        $json = $this->client()->get($base.'/users/'.rawurlencode($username).'/calendar.json')->throw()->json();
        if (! is_array($json)) {
            throw new \UnexpectedValueException('Unexpected GitLab response.');
        }

        $days = [];
        foreach ($json as $date => $count) {
            $this->addDay($days, $date, $count);
        }

        return $days;
    }

    /** @param  array<string, int>  $days */
    private function addDay(array &$days, mixed $date, mixed $count): void
    {
        if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 && is_int($count) && $count >= 0) {
            $days[$date] = $count;
        }
    }

    private function client(): PendingRequest
    {
        return $this->http->acceptJson()
            ->withHeaders(['User-Agent' => 'portfolio-activity-sync'])
            ->timeout(10)
            ->retry(2, 500, throw: false);
    }
}
