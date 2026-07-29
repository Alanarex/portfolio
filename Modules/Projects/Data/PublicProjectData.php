<?php

declare(strict_types=1);

namespace Modules\Projects\Data;

final readonly class PublicProjectData
{
    /**
     * @param  list<string>  $technologies
     * @param  array{start: array{year: int|null, month: int|null}, end: array{year: int|null, month: int|null}|null, ongoing: bool}  $dates
     * @param  list<array{type: string, heading: string, body: string}>  $sections
     * @param  list<array<string, mixed>>  $media
     */
    public function __construct(
        public string $slug,
        public string $title,
        public string $summary,
        public string $role,
        public string $lifecycleStatus,
        public array $technologies,
        public array $dates,
        public bool $featured,
        public ?string $repositoryUrl,
        public ?string $demoUrl,
        public array $sections,
        public array $media,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $data = [
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'role' => $this->role,
            'lifecycle_status' => $this->lifecycleStatus,
            'technologies' => $this->technologies,
            'dates' => $this->dates,
            'featured' => $this->featured,
            'sections' => $this->sections,
            'media' => $this->media,
        ];

        if ($this->repositoryUrl !== null) {
            $data['repository_url'] = $this->repositoryUrl;
        }
        if ($this->demoUrl !== null) {
            $data['demo_url'] = $this->demoUrl;
        }

        return $data;
    }

    public function toCachePayload(): string
    {
        return json_encode([
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'role' => $this->role,
            'lifecycle_status' => $this->lifecycleStatus,
            'technologies' => $this->technologies,
            'dates' => $this->dates,
            'featured' => $this->featured,
            'repository_url' => $this->repositoryUrl,
            'demo_url' => $this->demoUrl,
            'sections' => $this->sections,
            'media' => $this->media,
        ], JSON_THROW_ON_ERROR);
    }

    public static function fromCachePayload(string $payload): self
    {
        /**
         * @var array{
         *     slug: string,
         *     title: string,
         *     summary: string,
         *     role: string,
         *     lifecycle_status: string,
         *     technologies: list<string>,
         *     dates: array{start: array{year: int|null, month: int|null}, end: array{year: int|null, month: int|null}|null, ongoing: bool},
         *     featured: bool,
         *     repository_url: string|null,
         *     demo_url: string|null,
         *     sections: list<array{type: string, heading: string, body: string}>,
         *     media: list<array<string, mixed>>
         * } $data
         */
        $data = json_decode($payload, true, flags: JSON_THROW_ON_ERROR);

        return new self(
            slug: $data['slug'],
            title: $data['title'],
            summary: $data['summary'],
            role: $data['role'],
            lifecycleStatus: $data['lifecycle_status'],
            technologies: $data['technologies'],
            dates: $data['dates'],
            featured: $data['featured'],
            repositoryUrl: $data['repository_url'],
            demoUrl: $data['demo_url'],
            sections: $data['sections'],
            media: $data['media'],
        );
    }
}
