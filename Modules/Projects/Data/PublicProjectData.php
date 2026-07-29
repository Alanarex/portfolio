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
}
