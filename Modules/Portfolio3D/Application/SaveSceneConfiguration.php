<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Application;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Portfolio3D\Infrastructure\SceneCache;
use Modules\Portfolio3D\Models\SceneConfiguration;
use Modules\Portfolio3D\Models\SceneHotspot;

final class SaveSceneConfiguration
{
    private const FIELDS = ['enabled', 'asset_version', 'poster_path', 'mobile_fallback_path', 'greeting_fr', 'greeting_en', 'performance_mode', 'reduced_motion_mode'];

    public function __construct(private readonly AuditRecorder $auditRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor, ?string $requestId): SceneConfiguration
    {
        $changedFields = [];

        $configuration = DB::transaction(function () use ($data, $actor, $requestId, &$changedFields): SceneConfiguration {
            $configuration = SceneConfiguration::query()->where('key', 'main')->lockForUpdate()->first() ?? new SceneConfiguration(['key' => 'main']);
            $created = ! $configuration->exists;
            $configuration->fill(array_intersect_key($data, array_flip(self::FIELDS)));
            $changedFields = array_keys($configuration->getDirty());
            $configuration->save();

            /** @var list<array<string, mixed>> $hotspots */
            $hotspots = array_values($data['hotspots']);
            foreach ($hotspots as $attributes) {
                $hotspot = SceneHotspot::query()->firstOrNew([
                    'scene_configuration_id' => $configuration->getKey(),
                    'key' => $attributes['key'],
                ]);
                $hotspot->fill(array_diff_key($attributes, ['key' => true]));
                if ($hotspot->isDirty()) {
                    $changedFields[] = "hotspots.{$attributes['key']}";
                    $hotspot->save();
                }
            }

            $changedFields = array_values(array_unique(array_diff($changedFields, ['updated_at'])));
            if ($changedFields !== []) {
                $this->auditRecorder->record(
                    actor: $actor,
                    action: $created ? 'scene.created' : 'scene.updated',
                    subjectType: 'scene-configuration',
                    subjectId: 'main',
                    changedFields: $changedFields,
                    requestId: $requestId,
                );
            }

            return $configuration->load('hotspots');
        });

        if ($changedFields !== []) {
            SceneCache::invalidate();
        }

        return $configuration;
    }
}
