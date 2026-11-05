<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Infrastructure;

use Illuminate\Contracts\Config\Repository as Config;
use Modules\GitHubActivity\Contracts\PublicGitHubActivityReader;
use Modules\GitHubActivity\Data\PublicGitHubActivityData;
use Modules\GitHubActivity\Models\GitHubSnapshot;

final class DatabasePublicGitHubActivityReader implements PublicGitHubActivityReader
{
    public function __construct(private readonly Config $config) {}

    public function current(): ?PublicGitHubActivityData
    {
        $username = (string) $this->config->get('services.github.username');
        $snapshot = GitHubSnapshot::query()->where('username', $username)->first();
        if ($snapshot === null || ($snapshot->repositories === [] && $snapshot->events === [])) {
            return null;
        }

        /** @var list<array{name: string, url: string, description: ?string, language: ?string, stars: int, pushed_at: ?string}> $repositories */
        $repositories = $snapshot->repositories;
        /** @var list<array{type: string, repo: string, url: string, title: ?string, count: int, at: string}> $events */
        $events = $snapshot->events;

        return new PublicGitHubActivityData(
            username: $snapshot->username,
            repositories: $repositories,
            events: $events,
            syncedAt: $snapshot->synced_at->toDateTimeImmutable(),
        );
    }
}
