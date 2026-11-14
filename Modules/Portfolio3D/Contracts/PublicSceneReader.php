<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Contracts;

use Modules\Portfolio3D\Data\SceneManifestData;

interface PublicSceneReader
{
    /** Always returns a manifest: defaults apply until the administrator saves a configuration. */
    public function forLocale(string $locale): SceneManifestData;
}
