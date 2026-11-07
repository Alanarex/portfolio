<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Application;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Modules\GitHubActivity\Models\GitHubSnapshot;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Pulls public repositories and recent public events from the GitHub REST API and stores a
 * normalized snapshot. Only public data is requested; a failure keeps the previous snapshot.
 */
final class SyncGitHubActivity
{
    private const REPOSITORY_LIMIT = 6;

    private const EVENT_LIMIT = 8;

    public function __construct(
        private readonly Http $http,
        private readonly Config $config,
        private readonly LoggerInterface $logger,
    ) {}

    public function execute(): bool
    {
        $username = (string) $this->config->get('services.github.username');
        if (preg_match('/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,38})$/', $username) !== 1) {
            $this->logger->warning('GitHub activity sync skipped: invalid username.');

            return false;
        }

        try {
            $repositories = $this->client()
                ->get("/users/{$username}/repos", ['type' => 'owner', 'sort' => 'pushed', 'per_page' => 30])
                ->throw()
                ->json();
            $events = $this->client()
                ->get("/users/{$username}/events/public", ['per_page' => 50])
                ->throw()
                ->json();
        } catch (Throwable $exception) {
            $this->logger->warning('GitHub activity sync failed; previous snapshot kept.', ['exception' => $exception::class]);

            return false;
        }

        if (! is_array($repositories) || ! is_array($events)) {
            return false;
        }

        GitHubSnapshot::query()->updateOrCreate(['username' => $username], [
            'repositories' => $this->repositories($repositories),
            'events' => $this->events($events),
            'synced_at' => now(),
        ]);

        return true;
    }

    private function client(): PendingRequest
    {
        $request = $this->http->baseUrl('https://api.github.com')
            ->acceptJson()
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28', 'User-Agent' => 'portfolio-activity-sync'])
            ->timeout(10)
            ->retry(2, 500, throw: false);

        $token = $this->config->get('services.github.token');

        return is_string($token) && $token !== '' ? $request->withToken($token) : $request;
    }

    /**
     * @param  array<mixed>  $items
     * @return list<array{name: string, url: string, description: ?string, language: ?string, stars: int, pushed_at: ?string}>
     */
    private function repositories(array $items): array
    {
        $repositories = [];
        foreach ($items as $item) {
            if (! is_array($item) || ($item['private'] ?? true) !== false || ($item['fork'] ?? false) === true || ($item['archived'] ?? false) === true) {
                continue;
            }
            $name = $item['name'] ?? null;
            $url = $item['html_url'] ?? null;
            if (! is_string($name) || ! is_string($url) || ! str_starts_with($url, 'https://github.com/')) {
                continue;
            }

            $repositories[] = [
                'name' => $name,
                'url' => $url,
                'description' => is_string($item['description'] ?? null) ? mb_substr($item['description'], 0, 200) : null,
                'language' => is_string($item['language'] ?? null) ? $item['language'] : null,
                'stars' => is_int($item['stargazers_count'] ?? null) ? $item['stargazers_count'] : 0,
                'pushed_at' => is_string($item['pushed_at'] ?? null) ? $item['pushed_at'] : null,
            ];
            if (count($repositories) === self::REPOSITORY_LIMIT) {
                break;
            }
        }

        return $repositories;
    }

    /**
     * @param  array<mixed>  $items
     * @return list<array{type: string, repo: string, url: string, title: ?string, count: int, at: string}>
     */
    private function events(array $items): array
    {
        $events = [];
        foreach ($items as $item) {
            if (! is_array($item) || ($item['public'] ?? false) !== true) {
                continue;
            }
            $repo = Arr::get($item, 'repo.name');
            $at = $item['created_at'] ?? null;
            if (! is_string($repo) || ! is_string($at)) {
                continue;
            }
            $payload = is_array($item['payload'] ?? null) ? $item['payload'] : [];
            $repoUrl = 'https://github.com/'.$repo;

            $event = match ($item['type'] ?? null) {
                'PushEvent' => ['type' => 'push', 'url' => $repoUrl, 'title' => null, 'count' => $this->commitCount($payload)],
                'PullRequestEvent' => $this->pullRequest($payload, $repoUrl),
                'ReleaseEvent' => ($payload['action'] ?? null) === 'published'
                    ? ['type' => 'release', 'url' => $this->githubUrl(Arr::get($payload, 'release.html_url'), $repoUrl), 'title' => $this->text(Arr::get($payload, 'release.tag_name')), 'count' => 1]
                    : null,
                'CreateEvent' => ($payload['ref_type'] ?? null) === 'repository'
                    ? ['type' => 'repo_created', 'url' => $repoUrl, 'title' => null, 'count' => 1]
                    : null,
                default => null,
            };
            if ($event === null) {
                continue;
            }

            $events[] = ['type' => $event['type'], 'repo' => $repo, 'url' => $event['url'], 'title' => $event['title'], 'count' => $event['count'], 'at' => $at];
            if (count($events) === self::EVENT_LIMIT) {
                break;
            }
        }

        return $events;
    }

    /** @param  array<mixed>  $payload */
    private function commitCount(array $payload): int
    {
        if (is_int($payload['size'] ?? null)) {
            return max(1, $payload['size']);
        }

        return is_array($payload['commits'] ?? null) ? max(1, count($payload['commits'])) : 1;
    }

    /**
     * @param  array<mixed>  $payload
     * @return array{type: string, url: string, title: ?string, count: int}|null
     */
    private function pullRequest(array $payload, string $repoUrl): ?array
    {
        $action = $payload['action'] ?? null;
        $merged = Arr::get($payload, 'pull_request.merged') === true;
        $type = match (true) {
            $action === 'opened' => 'pr_opened',
            $action === 'closed' && $merged => 'pr_merged',
            default => null,
        };

        return $type === null ? null : [
            'type' => $type,
            'url' => $this->githubUrl(Arr::get($payload, 'pull_request.html_url'), $repoUrl),
            'title' => $this->text(Arr::get($payload, 'pull_request.title')),
            'count' => 1,
        ];
    }

    private function githubUrl(mixed $url, string $fallback): string
    {
        return is_string($url) && str_starts_with($url, 'https://github.com/') ? $url : $fallback;
    }

    private function text(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? mb_substr($value, 0, 160) : null;
    }
}
