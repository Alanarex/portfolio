<?php

declare(strict_types=1);

namespace Modules\Posts\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Posts\Database\Factories\PostFactory;
use Modules\Posts\Enums\PostStatus;

/**
 * @property int $id
 * @property string $locale
 * @property string $body
 * @property string|null $link_url
 * @property string|null $link_label
 * @property PostStatus $status
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 */
#[Fillable(['locale', 'body', 'link_url', 'link_label', 'status', 'published_at'])]
final class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }
}
