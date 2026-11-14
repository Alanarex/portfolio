<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Data;

final readonly class SceneManifestData
{
    /**
     * @param  list<array{key: string, label: string, description: string, target: string, href: string}>  $hotspots
     */
    public function __construct(
        public bool $enabled,
        public string $locale,
        public string $assetVersion,
        public ?string $poster,
        public ?string $mobileFallback,
        public string $greeting,
        public string $performanceMode,
        public string $reducedMotionMode,
        public array $hotspots,
    ) {}

    /**
     * Returns a copy without the hotspots whose target section is not rendered on the page.
     *
     * @param  list<string>  $targets
     */
    public function withoutTargets(array $targets): self
    {
        return new self(
            $this->enabled,
            $this->locale,
            $this->assetVersion,
            $this->poster,
            $this->mobileFallback,
            $this->greeting,
            $this->performanceMode,
            $this->reducedMotionMode,
            array_values(array_filter($this->hotspots, fn (array $hotspot): bool => ! in_array($hotspot['target'], $targets, true))),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'version' => 1,
            'enabled' => $this->enabled,
            'locale' => $this->locale,
            'assetVersion' => $this->assetVersion,
            'poster' => $this->poster,
            'mobileFallback' => $this->mobileFallback,
            'greeting' => $this->greeting,
            'performanceMode' => $this->performanceMode,
            'reducedMotion' => $this->reducedMotionMode,
            'hotspots' => $this->hotspots,
        ];
    }
}
