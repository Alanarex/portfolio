<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\ActivityLog\Models\AuditEvent;
use Modules\Portfolio3D\Contracts\PublicSceneReader;
use Modules\Portfolio3D\Enums\HotspotKey;
use Modules\Portfolio3D\Infrastructure\SceneCache;
use Modules\Portfolio3D\Models\SceneConfiguration;
use Modules\Settings\Models\SiteSetting;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class SceneConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_scene_routes_require_an_administrator(): void
    {
        $this->get('/dashboard/scene')->assertRedirect('/login');
        $this->put('/dashboard/scene', $this->payload())->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['auth_version' => $user->auth_version])->get('/dashboard/scene')->assertForbidden();
        $this->actingAs($user)->withSession(['auth_version' => $user->auth_version])->put('/dashboard/scene', $this->payload())->assertForbidden();
        $this->assertDatabaseCount('scene_configurations', 0);

        $this->asAdministrator()->get('/dashboard/scene')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Scene/Edit')
                ->where('scene.enabled', false)
                ->has('scene.hotspots', 11));
    }

    public function test_administrator_saves_validated_configuration_with_redacted_audit_and_cache_invalidation(): void
    {
        $reader = app(PublicSceneReader::class);
        $reader->forLocale('fr');
        $this->assertTrue(Cache::has(SceneCache::key('fr')));

        $payload = $this->payload();
        $payload['greeting_fr'] = 'Salut, visiteur !';
        $payload['hotspots'][0]['label_fr'] = 'Écran de projets';
        $payload['hotspots'][1]['is_visible'] = false;

        $this->asAdministrator()->put('/dashboard/scene', $payload)->assertRedirect()->assertSessionHasNoErrors();

        $this->assertFalse(Cache::has(SceneCache::key('fr')));
        $configuration = SceneConfiguration::query()->where('key', 'main')->firstOrFail();
        $this->assertTrue($configuration->enabled);
        $this->assertSame('/assets/room/poster.webp', $configuration->poster_path);
        $this->assertCount(11, $configuration->hotspots);

        $manifest = $reader->forLocale('fr');
        $this->assertSame('Salut, visiteur !', $manifest->greeting);
        $this->assertSame('Écran de projets', $manifest->hotspots[0]['label']);
        $this->assertNotContains('secondary_monitor', array_column($manifest->hotspots, 'key'));
        $this->assertCount(10, $manifest->hotspots);
        // Pas de repli entre langues : l'anglais garde ses libellés par défaut.
        $this->assertSame('Main monitor', $reader->forLocale('en')->hotspots[0]['label']);

        $event = AuditEvent::query()->where('action', 'scene.created')->firstOrFail();
        $this->assertSame('scene-configuration', $event->subject_type);
        $this->assertStringNotContainsString('Salut', json_encode($event->getAttributes(), JSON_THROW_ON_ERROR));
    }

    /** @return iterable<string, array{string, mixed}> */
    public static function invalidInputs(): iterable
    {
        yield 'path traversal poster' => ['poster_path', '/assets/../.env.webp'];
        yield 'external poster' => ['poster_path', 'https://evil.test/p.webp'];
        yield 'script poster' => ['poster_path', '/assets/room/p.svg'];
        yield 'bad version' => ['asset_version', 'Bad Version!'];
        yield 'bad performance mode' => ['performance_mode', 'turbo'];
        yield 'bad reduced motion' => ['reduced_motion_mode', 'never'];
        yield 'long greeting' => ['greeting_fr', str_repeat('a', 281)];
    }

    #[DataProvider('invalidInputs')]
    public function test_invalid_scene_settings_are_rejected(string $field, mixed $value): void
    {
        $payload = $this->payload();
        $payload[$field] = $value;

        $this->asAdministrator()->put('/dashboard/scene', $payload)->assertSessionHasErrors($field);
        $this->assertDatabaseCount('scene_configurations', 0);
    }

    public function test_hotspot_targets_and_keys_are_allowlisted(): void
    {
        $payload = $this->payload();
        $payload['hotspots'][0]['target'] = 'https://evil.test';
        $this->asAdministrator()->put('/dashboard/scene', $payload)->assertSessionHasErrors('hotspots.0.target');

        $payload = $this->payload();
        $payload['hotspots'][0]['key'] = 'unknown_object';
        $this->asAdministrator()->put('/dashboard/scene', $payload)->assertSessionHasErrors('hotspots.0.key');

        $payload = $this->payload();
        array_pop($payload['hotspots']);
        $this->asAdministrator()->put('/dashboard/scene', $payload)->assertSessionHasErrors('hotspots');
    }

    public function test_scene_is_disabled_by_default_and_hidden_from_the_public_page(): void
    {
        $this->settings(activity: true);

        $this->get('/fr')->assertOk()->assertDontSee('data-room', false);
        $this->get('/fr/scene.json')->assertNotFound();
    }

    public function test_enabled_scene_renders_html_equivalent_for_every_hotspot_and_a_manifest(): void
    {
        $this->settings(activity: true);
        $this->asAdministrator()->put('/dashboard/scene', $this->payload())->assertSessionHasNoErrors();

        $response = $this->get('/fr')->assertOk()->assertSee('data-room', false)->assertSee('data-room-manifest', false);
        foreach (HotspotKey::values() as $key) {
            $response->assertSee('data-room-hotspot="'.$key.'"', false);
        }

        $this->getJson('/en/scene.json')
            ->assertOk()
            ->assertHeader('Cache-Control')
            ->assertJsonPath('version', 1)
            ->assertJsonPath('locale', 'en')
            ->assertJsonCount(11, 'hotspots')
            ->assertJsonPath('hotspots.0.href', url('/en').'#projets');
    }

    public function test_hotspots_pointing_to_a_missing_activity_section_are_omitted(): void
    {
        $this->settings(activity: false);
        $this->asAdministrator()->put('/dashboard/scene', $this->payload())->assertSessionHasNoErrors();

        $this->get('/fr')->assertOk()->assertDontSee('data-room-hotspot="secondary_monitor"', false);
        $this->getJson('/fr/scene.json')->assertOk()->assertJsonCount(10, 'hotspots');
    }

    public function test_manifest_payload_never_contains_private_settings(): void
    {
        $this->settings(activity: true);
        $this->asAdministrator()->put('/dashboard/scene', $this->payload())->assertSessionHasNoErrors();

        $this->get('/fr')->assertDontSee('contact@example.test');
        $this->get('/fr/scene.json')->assertDontSee('contact@example.test');
    }

    private function asAdministrator(): self
    {
        $administrator = User::factory()->administrator()->create();

        return $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version]);
    }

    private function settings(bool $activity): void
    {
        $settings = SiteSetting::query()->create([
            'key' => 'main',
            'site_name' => 'Alaa Khalil',
            'default_locale' => 'fr',
            'contact_email' => 'contact@example.test',
            'show_email' => false,
            'show_phone' => false,
        ]);
        $settings->featureFlags()->createMany([
            ['key' => 'activity', 'enabled' => $activity],
            ['key' => 'three_d', 'enabled' => false],
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'enabled' => true,
            'asset_version' => 'placeholder-1',
            'poster_path' => '/assets/room/poster.webp',
            'mobile_fallback_path' => null,
            'greeting_fr' => null,
            'greeting_en' => null,
            'performance_mode' => 'auto',
            'reduced_motion_mode' => 'respect',
            'hotspots' => array_map(fn (HotspotKey $key): array => [
                'key' => $key->value,
                'target' => $key->defaultTarget(),
                'is_visible' => true,
                'sort_order' => $key->defaultOrder(),
                'label_fr' => null,
                'label_en' => null,
                'description_fr' => null,
                'description_en' => null,
            ], HotspotKey::cases()),
        ];
    }
}
