<?php

declare(strict_types=1);

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Settings\Database\Factories\PublicFeatureFlagFactory;

/**
 * @property int $id
 * @property int $site_setting_id
 * @property string $key
 * @property bool $enabled
 */
#[Fillable(['site_setting_id', 'key', 'enabled'])]
final class PublicFeatureFlag extends Model
{
    /** @use HasFactory<PublicFeatureFlagFactory> */
    use HasFactory;

    protected static function newFactory(): PublicFeatureFlagFactory
    {
        return PublicFeatureFlagFactory::new();
    }

    /** @return BelongsTo<SiteSetting, $this> */
    public function siteSetting(): BelongsTo
    {
        return $this->belongsTo(SiteSetting::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
