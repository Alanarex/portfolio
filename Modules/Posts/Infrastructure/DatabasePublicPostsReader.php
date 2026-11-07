<?php

declare(strict_types=1);

namespace Modules\Posts\Infrastructure;

use Modules\Posts\Contracts\PublicPostsReader;
use Modules\Posts\Data\PublicPostData;
use Modules\Posts\Enums\PostStatus;
use Modules\Posts\Models\Post;

final class DatabasePublicPostsReader implements PublicPostsReader
{
    public function latest(string $locale, int $limit = 10): array
    {
        return Post::query()
            ->where('locale', $locale)
            ->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (Post $post): PublicPostData => new PublicPostData(
                id: $post->id,
                body: $post->body,
                publishedAt: $post->published_at?->toDateTimeImmutable() ?? now()->toDateTimeImmutable(),
                linkUrl: $post->link_url,
                linkLabel: $post->link_label,
            ))
            ->values()
            ->all();
    }
}
