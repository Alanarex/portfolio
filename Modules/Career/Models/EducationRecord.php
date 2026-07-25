<?php

declare(strict_types=1);

namespace Modules\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Career\Database\Factories\EducationRecordFactory;
use Modules\Career\Enums\PublicationStatus;

/**
 * @property int $id
 * @property string $key
 * @property string $institution
 * @property int $start_year
 * @property int|null $start_month
 * @property int|null $end_year
 * @property int|null $end_month
 * @property PublicationStatus $status
 * @property int $sort_order
 * @property-read Collection<int, EducationRecordTranslation> $translations
 */
#[Fillable(['key', 'institution', 'start_year', 'start_month', 'end_year', 'end_month', 'status', 'sort_order', 'published_at'])]
final class EducationRecord extends Model
{
    /** @use HasFactory<EducationRecordFactory> */
    use HasFactory;

    protected static function newFactory(): EducationRecordFactory
    {
        return EducationRecordFactory::new();
    }

    /** @return HasMany<EducationRecordTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(EducationRecordTranslation::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => PublicationStatus::class, 'published_at' => 'immutable_datetime'];
    }
}
