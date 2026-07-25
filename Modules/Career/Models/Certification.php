<?php

declare(strict_types=1);

namespace Modules\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Career\Database\Factories\CertificationFactory;
use Modules\Career\Enums\PublicationStatus;

/**
 * @property int $id
 * @property string $key
 * @property string|null $issuer
 * @property string|null $credential_id
 * @property string|null $verification_url
 * @property int $issue_year
 * @property int|null $issue_month
 * @property string|null $result
 * @property bool $is_verified
 * @property string|null $source_reference
 * @property PublicationStatus $status
 * @property int $sort_order
 * @property-read Collection<int, CertificationTranslation> $translations
 */
#[Fillable(['key', 'issuer', 'credential_id', 'verification_url', 'issue_year', 'issue_month', 'result', 'is_verified', 'source_reference', 'status', 'sort_order', 'published_at'])]
final class Certification extends Model
{
    /** @use HasFactory<CertificationFactory> */
    use HasFactory;

    protected static function newFactory(): CertificationFactory
    {
        return CertificationFactory::new();
    }

    /** @return HasMany<CertificationTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(CertificationTranslation::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => PublicationStatus::class, 'is_verified' => 'boolean', 'published_at' => 'immutable_datetime'];
    }
}
