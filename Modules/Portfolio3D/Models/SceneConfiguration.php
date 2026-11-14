<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $key
 * @property bool $enabled
 * @property string $asset_version
 * @property string|null $poster_path
 * @property string|null $mobile_fallback_path
 * @property string|null $greeting_fr
 * @property string|null $greeting_en
 * @property string $performance_mode
 * @property string $reduced_motion_mode
 */
#[Fillable(['key', 'enabled', 'asset_version', 'poster_path', 'mobile_fallback_path', 'greeting_fr', 'greeting_en', 'performance_mode', 'reduced_motion_mode'])]
final class SceneConfiguration extends Model
{
    /** @return HasMany<SceneHotspot, $this> */
    public function hotspots(): HasMany
    {
        return $this->hasMany(SceneHotspot::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
