<?php

declare(strict_types=1);

namespace Modules\Career\Data;

final readonly class PublicCareerData
{
    /**
     * @param  list<array<string, mixed>>  $experiences
     * @param  list<array<string, mixed>>  $education
     * @param  list<array<string, mixed>>  $certifications
     * @param  list<array<string, mixed>>  $languages
     */
    public function __construct(
        public array $experiences,
        public array $education,
        public array $certifications,
        public array $languages,
    ) {}

    /** @return array{experiences: list<array<string, mixed>>, education: list<array<string, mixed>>, certifications: list<array<string, mixed>>, languages: list<array<string, mixed>>} */
    public function toArray(): array
    {
        return [
            'experiences' => $this->experiences,
            'education' => $this->education,
            'certifications' => $this->certifications,
            'languages' => $this->languages,
        ];
    }
}
