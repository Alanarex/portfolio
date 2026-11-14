<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Portfolio3D\Application\SaveSceneConfiguration;
use Modules\Portfolio3D\Enums\HotspotKey;
use Modules\Portfolio3D\Http\Requests\UpdateSceneRequest;
use Modules\Portfolio3D\Models\SceneConfiguration;

final class SceneController extends Controller
{
    public function edit(): Response
    {
        Gate::authorize('viewAny', SceneConfiguration::class);
        $configuration = SceneConfiguration::query()->where('key', 'main')->with('hotspots')->first();
        $stored = $configuration?->hotspots->keyBy('key');

        $hotspots = collect(HotspotKey::cases())->map(function (HotspotKey $case) use ($stored): array {
            $row = $stored?->get($case->value);

            return [
                'key' => $case->value,
                'target' => $row->target ?? $case->defaultTarget(),
                'is_visible' => $row->is_visible ?? true,
                'sort_order' => $row->sort_order ?? $case->defaultOrder(),
                'label_fr' => $row->label_fr ?? '',
                'label_en' => $row->label_en ?? '',
                'description_fr' => $row->description_fr ?? '',
                'description_en' => $row->description_en ?? '',
            ];
        })->sortBy('sort_order')->values();

        return Inertia::render('Admin/Scene/Edit', [
            'scene' => [
                'enabled' => $configuration->enabled ?? false,
                'asset_version' => $configuration->asset_version ?? 'placeholder-1',
                'poster_path' => $configuration->poster_path ?? '',
                'mobile_fallback_path' => $configuration->mobile_fallback_path ?? '',
                'greeting_fr' => $configuration->greeting_fr ?? '',
                'greeting_en' => $configuration->greeting_en ?? '',
                'performance_mode' => $configuration->performance_mode ?? 'auto',
                'reduced_motion_mode' => $configuration->reduced_motion_mode ?? 'respect',
                'hotspots' => $hotspots,
            ],
            'targets' => HotspotKey::TARGETS,
        ]);
    }

    public function update(UpdateSceneRequest $request, SaveSceneConfiguration $action): RedirectResponse
    {
        Gate::authorize('update', new SceneConfiguration);
        $user = $request->user();
        abort_unless($user !== null, 403);
        $action->execute($request->validated(), $user, $request->attributes->get('request_id'));

        return back()->with('success', 'Scène 3D enregistrée.');
    }
}
