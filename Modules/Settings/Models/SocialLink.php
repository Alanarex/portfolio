<?php

declare(strict_types=1);

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Settings\Database\Factories\SocialLinkFactory;

/**
 * @property int $id
 * @property int $site_setting_id
 * @property string $platform
 * @property string $url
 * @property bool $is_enabled
 * @property bool $is_public
 * @property int $sort_order
 */
#[Fillable(['site_setting_id', 'platform', 'url', 'is_enabled', 'is_public', 'sort_order'])]
final class SocialLink extends Model
{
    /** @use HasFactory<SocialLinkFactory> */
    use HasFactory;

    protected static function newFactory(): SocialLinkFactory
    {
        return SocialLinkFactory::new();
    }

    /** @return BelongsTo<SiteSetting, $this> */
    public function siteSetting(): BelongsTo
    {
        return $this->belongsTo(SiteSetting::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'url' => 'encrypted',
            'is_enabled' => 'boolean',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
