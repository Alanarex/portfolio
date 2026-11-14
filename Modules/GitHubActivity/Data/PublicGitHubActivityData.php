<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Data;

use DateTimeImmutable;

final readonly class PublicGitHubActivityData
{
    /**
     * @param  list<array{name: string, url: string, description: ?string, language: ?string, stars: int, pushed_at: ?string, project_slug?: string}>  $repositories
     * @param  list<array{type: string, repo: string, url: string, title: ?string, count: int, at: string}>  $events
     */
    public function __construct(
        public string $username,
        public array $repositories,
        public array $events,
        public DateTimeImmutable $syncedAt,
    ) {}
}
