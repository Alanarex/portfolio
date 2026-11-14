<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Portfolio3D\Contracts\PublicSceneReader;
use Modules\Settings\Contracts\PublicSettingsReader;

final class SceneManifestController extends Controller
{
    public function __construct(
        private readonly PublicSceneReader $scenes,
        private readonly PublicSettingsReader $settings,
    ) {}

    public function show(string $locale): JsonResponse
    {
        $manifest = $this->scenes->forLocale($locale);
        abort_unless($manifest->enabled, 404);

        if (($this->settings->forLocale($locale)?->featureFlags['activity'] ?? false) !== true) {
            $manifest = $manifest->withoutTargets(['activite']);
        }

        return response()->json($manifest->toArray())->header('Cache-Control', 'public, max-age=300');
    }
}
