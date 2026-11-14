<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Modules\Portfolio3D\Contracts\PublicSceneReader;
use Modules\Portfolio3D\Data\SceneManifestData;
use Modules\Portfolio3D\Enums\HotspotKey;
use Modules\Portfolio3D\Models\SceneConfiguration;

final class DatabasePublicSceneReader implements PublicSceneReader
{
    public function forLocale(string $locale): SceneManifestData
    {
        $locale = $locale === 'en' ? 'en' : 'fr';

        /** @var array<string, mixed> $payload */
        $payload = Cache::remember(SceneCache::key($locale), now()->addMinutes(5), fn (): array => $this->build($locale));

        /** @var list<array{key: string, label: string, description: string, target: string, href: string}> $hotspots */
        $hotspots = $payload['hotspots'];

        return new SceneManifestData(
            enabled: (bool) $payload['enabled'],
            locale: $locale,
            assetVersion: (string) $payload['asset_version'],
            poster: $payload['poster'] === null ? null : (string) $payload['poster'],
            mobileFallback: $payload['mobile_fallback'] === null ? null : (string) $payload['mobile_fallback'],
            greeting: (string) $payload['greeting'],
            performanceMode: (string) $payload['performance_mode'],
            reducedMotionMode: (string) $payload['reduced_motion_mode'],
            hotspots: $hotspots,
        );
    }

    /** @return array<string, mixed> */
    private function build(string $locale): array
    {
        $configuration = SceneConfiguration::query()->where('key', 'main')->with('hotspots')->first();
        $home = route('portfolio.home', ['locale' => $locale]);
        $stored = $configuration?->hotspots->keyBy('key');

        $hotspots = [];
        foreach (HotspotKey::cases() as $case) {
            $row = $stored?->get($case->value);
            if ($row !== null && ! $row->is_visible) {
                continue;
            }
            $label = $row === null ? null : $row->{"label_{$locale}"};
            $description = $row === null ? null : $row->{"description_{$locale}"};
            $target = $row === null ? $case->defaultTarget() : $row->target;
            $hotspots[] = [
                'order' => $row === null ? $case->defaultOrder() : $row->sort_order,
                'key' => $case->value,
                'label' => filled($label) ? (string) $label : (string) trans("portfolio.room.hotspots.{$case->value}.label", [], $locale),
                'description' => filled($description) ? (string) $description : (string) trans("portfolio.room.hotspots.{$case->value}.description", [], $locale),
                'target' => $target,
                'href' => $home.'#'.$target,
            ];
        }
        usort($hotspots, fn (array $a, array $b): int => $a['order'] <=> $b['order']);
        $hotspots = array_map(function (array $hotspot): array {
            unset($hotspot['order']);

            return $hotspot;
        }, $hotspots);

        $greeting = $configuration === null ? null : $configuration->{"greeting_{$locale}"};

        return [
            'enabled' => $configuration->enabled ?? false,
            'asset_version' => $configuration->asset_version ?? 'placeholder-1',
            'poster' => $configuration?->poster_path,
            'mobile_fallback' => $configuration?->mobile_fallback_path,
            'greeting' => filled($greeting) ? (string) $greeting : (string) trans('portfolio.room.greeting', [], $locale),
            'performance_mode' => $configuration->performance_mode ?? 'auto',
            'reduced_motion_mode' => $configuration->reduced_motion_mode ?? 'respect',
            'hotspots' => $hotspots,
        ];
    }
}
