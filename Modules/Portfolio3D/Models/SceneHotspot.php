<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $scene_configuration_id
 * @property string $key
 * @property string $target
 * @property bool $is_visible
 * @property int $sort_order
 * @property string|null $label_fr
 * @property string|null $label_en
 * @property string|null $description_fr
 * @property string|null $description_en
 */
#[Fillable(['scene_configuration_id', 'key', 'target', 'is_visible', 'sort_order', 'label_fr', 'label_en', 'description_fr', 'description_en'])]
final class SceneHotspot extends Model
{
    /** @return BelongsTo<SceneConfiguration, $this> */
    public function sceneConfiguration(): BelongsTo
    {
        return $this->belongsTo(SceneConfiguration::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'sort_order' => 'integer'];
    }
}
