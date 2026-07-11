<?php

declare(strict_types=1);

namespace App\Core\Modules;

use Illuminate\Support\ServiceProvider;
use RuntimeException;

final class ModuleDiscovery
{
    /** @return list<class-string<ServiceProvider>> */
    public static function providers(string $basePath): array
    {
        $manifests = glob($basePath.'/Modules/*/module.json') ?: [];
        sort($manifests);
        $providers = [];
        foreach ($manifests as $manifest) {
            $data = json_decode((string) file_get_contents($manifest), true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($data) || ! is_string($data['name'] ?? null) || ! is_string($data['provider'] ?? null)) {
                throw new RuntimeException("Invalid module manifest: {$manifest}");
            }
            $provider = $data['provider'];
            if (! is_subclass_of($provider, ServiceProvider::class)) {
                throw new RuntimeException("Invalid module provider [{$provider}] in {$manifest}");
            }
            $providers[] = $provider;
        }

        return $providers;
    }
}
