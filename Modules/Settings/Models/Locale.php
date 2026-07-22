<?php

declare(strict_types=1);

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $code
 * @property string $label
 * @property string $direction
 * @property string $status
 */
#[Fillable(['code', 'label', 'direction', 'status'])]
final class Locale extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';
}
