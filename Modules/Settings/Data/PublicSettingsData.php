<?php

declare(strict_types=1);

namespace Modules\Settings\Data;

final readonly class PublicSettingsData
{
    /**
     * @param  array<string, string>  $socialLinks
     * @param  array<string, bool>  $featureFlags
     */
    public function __construct(
        public string $siteName,
        public string $locale,
        public ?string $email,
        public ?string $phone,
        public array $socialLinks,
        public array $featureFlags,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'site_name' => $this->siteName,
            'locale' => $this->locale,
            'social_links' => $this->socialLinks,
            'feature_flags' => $this->featureFlags,
        ];

        if ($this->email !== null) {
            $data['email'] = $this->email;
        }

        if ($this->phone !== null) {
            $data['phone'] = $this->phone;
        }

        return $data;
    }
}
