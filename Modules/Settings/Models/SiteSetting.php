<?php

declare(strict_types=1);

namespace Modules\Settings\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Settings\Database\Factories\SiteSettingFactory;

/**
 * @property int $id
 * @property string $key
 * @property string $site_name
 * @property string $default_locale
 * @property string|null $contact_email
 * @property string|null $contact_phone
 * @property bool $show_email
 * @property bool $show_phone
 * @property-read Collection<int, SocialLink> $socialLinks
 * @property-read Collection<int, PublicFeatureFlag> $featureFlags
 */
#[Fillable(['key', 'site_name', 'default_locale', 'contact_email', 'contact_phone', 'show_email', 'show_phone'])]
final class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    protected static function newFactory(): SiteSettingFactory
    {
        return SiteSettingFactory::new();
    }

    /** @return HasMany<SocialLink, $this> */
    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class)->orderBy('sort_order');
    }

    /** @return HasMany<PublicFeatureFlag, $this> */
    public function featureFlags(): HasMany
    {
        return $this->hasMany(PublicFeatureFlag::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'contact_email' => 'encrypted',
            'contact_phone' => 'encrypted',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
        ];
    }
}
