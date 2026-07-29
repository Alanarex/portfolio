<?php

declare(strict_types=1);

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $media_asset_id
 * @property string $locale
 * @property string|null $alt_text
 * @property string|null $caption
 */
#[Fillable(['media_asset_id', 'locale', 'alt_text', 'caption'])]
final class MediaAssetTranslation extends Model {}
