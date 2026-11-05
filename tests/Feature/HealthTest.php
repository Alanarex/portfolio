<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_reports_every_dependency_without_a_session(): void
    {
        $response = $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database', 'ok')
            ->assertJsonPath('checks.cache', 'ok')
            ->assertJsonPath('checks.storage', 'ok')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        self::assertContains($response->json('checks.redis'), ['ok', 'skipped']);
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $response->assertCookieMissing(config('session.cookie'));
    }

    public function test_health_fails_closed_without_leaking_connection_details(): void
    {
        $default = config('database.default');

        config([
            'database.connections.broken' => [
                'driver' => 'pgsql',
                'host' => '127.0.0.1',
                'port' => 1,
                'database' => 'missing',
                'username' => 'nobody',
                'password' => 'secret-value',
            ],
            'database.default' => 'broken',
        ]);

        try {
            $response = $this->getJson('/health');
        } finally {
            // RefreshDatabase rolls back on the default connection at tear down.
            config(['database.default' => $default]);
        }

        $response->assertStatus(503)
            ->assertJsonPath('status', 'failed')
            ->assertJsonPath('checks.database', 'failed');

        self::assertStringNotContainsString('secret-value', (string) $response->getContent());
        self::assertStringNotContainsString('127.0.0.1', (string) $response->getContent());
    }

    public function test_health_command_exits_successfully_when_healthy(): void
    {
        $this->artisan('portfolio:health')
            ->expectsOutputToContain('database')
            ->assertSuccessful();
    }
}
