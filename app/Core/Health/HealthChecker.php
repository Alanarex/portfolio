<?php

declare(strict_types=1);

namespace App\Core\Health;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Throwable;

/**
 * Runs the dependency checks behind /health and `portfolio:health`.
 *
 * Results never contain exception messages, hosts or credentials: only a status per check.
 */
final class HealthChecker
{
    public const OK = 'ok';

    public const FAILED = 'failed';

    public const SKIPPED = 'skipped';

    public function __construct(
        private readonly DatabaseManager $database,
        private readonly CacheFactory $cache,
        private readonly Config $config,
    ) {}

    /** @return array{status: string, checks: array<string, string>} */
    public function run(): array
    {
        $checks = [
            'database' => $this->check(fn (): bool => $this->database->connection()->select('select 1 as ok') !== []),
            'cache' => $this->check(function (): bool {
                $key = 'health:'.Str::random(12);
                $store = $this->cache->store();
                $store->put($key, 'ok', 10);
                $value = $store->get($key);
                $store->forget($key);

                return $value === 'ok';
            }),
            'redis' => $this->usesRedis()
                ? $this->check(fn (): bool => (bool) Redis::connection()->ping())
                : self::SKIPPED,
            'storage' => $this->check(fn (): bool => is_writable(storage_path('framework'))),
        ];

        $healthy = ! in_array(self::FAILED, $checks, true);

        return ['status' => $healthy ? self::OK : self::FAILED, 'checks' => $checks];
    }

    private function usesRedis(): bool
    {
        return in_array('redis', [
            $this->config->get('cache.default'),
            $this->config->get('queue.default'),
            $this->config->get('session.driver'),
        ], true);
    }

    /** @param callable(): bool $probe */
    private function check(callable $probe): string
    {
        try {
            return $probe() ? self::OK : self::FAILED;
        } catch (Throwable) {
            return self::FAILED;
        }
    }
}
