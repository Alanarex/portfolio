<?php

declare(strict_types=1);

namespace Modules\Profile\Data;

final readonly class PublicProfileData
{
    /** @param list<string> $professionalTitles */
    public function __construct(
        public string $displayName,
        public array $professionalTitles,
        public string $summary,
        public string $biography,
        public ?string $location,
        public ?string $availability,
    ) {}

    /** @return array{display_name: string, professional_titles: list<string>, summary: string, biography: string, location?: string, availability?: string} */
    public function toArray(): array
    {
        $data = [
            'display_name' => $this->displayName,
            'professional_titles' => $this->professionalTitles,
            'summary' => $this->summary,
            'biography' => $this->biography,
        ];

        if ($this->location !== null) {
            $data['location'] = $this->location;
        }

        if ($this->availability !== null) {
            $data['availability'] = $this->availability;
        }

        return $data;
    }
}
