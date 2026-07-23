<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\ActivityLog\Models\AuditEvent;
use Modules\Profile\Contracts\PublicProfileReader;
use Modules\Profile\Models\CvVersion;
use Modules\Settings\Contracts\PublicSettingsReader;
use Modules\Settings\Models\Locale;
use Modules\Settings\Models\SiteSetting;
use Tests\TestCase;

final class ProfileSettingsCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_routes_require_an_administrator(): void
    {
        $this->get('/dashboard/profile')->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)
            ->withSession(['auth_version' => $user->auth_version])
            ->get('/dashboard/profile')
            ->assertForbidden();

        $administrator = User::factory()->administrator()->create();
        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->get('/dashboard/profile')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Profile/Edit')
                ->where('profile.is_published', false)
                ->has('cvVersions', 0));

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->get('/dashboard/settings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Settings/Edit')
                ->where('settings.default_locale', 'fr')
                ->where('settings.feature_flags.cv', false));
    }

    public function test_administrator_can_create_and_update_the_single_profile(): void
    {
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/profile', $this->profilePayload())
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseCount('profile_translations', 2);
        $this->assertDatabaseHas('profiles', [
            'key' => 'main',
            'display_name' => 'Alaa Khalil',
            'is_published' => true,
            'show_location' => false,
        ]);

        $payload = $this->profilePayload();
        $payload['translations']['fr']['summary'] = 'Résumé professionnel mis à jour.';
        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/profile', $payload)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('profiles', 1);
        $this->assertDatabaseHas('profile_translations', [
            'locale' => 'fr',
            'summary' => 'Résumé professionnel mis à jour.',
        ]);

        $events = AuditEvent::query()->where('subject_type', 'profile')->get();
        self::assertCount(2, $events);
        self::assertSame(['translations.fr.summary'], $events->last()?->changed_fields);
        self::assertStringNotContainsString('Résumé professionnel mis à jour.', $events->toJson());
    }

    public function test_public_profile_reader_is_published_locale_aware_and_private_by_default(): void
    {
        $administrator = User::factory()->administrator()->create();
        $reader = $this->app->make(PublicProfileReader::class);
        self::assertNull($reader->forLocale('fr'));

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/profile', $this->profilePayload())
            ->assertSessionHasNoErrors();

        $public = $reader->forLocale('en')?->toArray();
        self::assertSame('Alaa Khalil', $public['display_name'] ?? null);
        self::assertSame('English public summary.', $public['summary'] ?? null);
        self::assertArrayNotHasKey('location', $public ?? []);
        self::assertArrayNotHasKey('availability', $public ?? []);
        self::assertNull($reader->forLocale('ar'));

        $payload = $this->profilePayload();
        $payload['show_location'] = true;
        $payload['show_availability'] = true;
        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/profile', $payload)
            ->assertSessionHasNoErrors();

        $refreshed = $reader->forLocale('en')?->toArray();
        self::assertSame('France', $refreshed['location'] ?? null);
        self::assertSame('Available for selected work.', $refreshed['availability'] ?? null);
    }

    public function test_vue_shaped_empty_arabic_readiness_fields_do_not_block_profile_save(): void
    {
        $administrator = User::factory()->administrator()->create();
        $payload = $this->profilePayload();
        $payload['translations']['ar'] = [
            'professional_titles' => [''],
            'summary' => '',
            'location_label' => '',
            'availability' => '',
            'biography' => '',
        ];

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/profile', $payload)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('profile_translations', 2);
        $this->assertDatabaseMissing('profile_translations', ['locale' => 'ar']);
    }

    public function test_partial_arabic_readiness_content_is_stored_without_becoming_public(): void
    {
        $administrator = User::factory()->administrator()->create();
        $payload = $this->profilePayload();
        $payload['translations']['ar'] = [
            'professional_titles' => ['مطور Laravel'],
            'summary' => null,
            'location_label' => null,
            'availability' => null,
            'biography' => null,
        ];

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/profile', $payload)
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('profile_translations', [
            'locale' => 'ar',
            'summary' => '',
            'biography' => '',
        ]);
        self::assertNull($this->app->make(PublicProfileReader::class)->forLocale('ar'));
    }

    public function test_settings_are_validated_encrypted_and_publicly_allowlisted(): void
    {
        $administrator = User::factory()->administrator()->create();
        $reader = $this->app->make(PublicSettingsReader::class);

        self::assertSame('active', Locale::query()->findOrFail('fr')->status);
        self::assertSame('active', Locale::query()->findOrFail('en')->status);
        self::assertSame('readiness', Locale::query()->findOrFail('ar')->status);
        self::assertNull($reader->forLocale('ar'));

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/settings', $this->settingsPayload())
            ->assertSessionHasNoErrors();

        $settings = SiteSetting::query()->where('key', 'main')->firstOrFail();
        self::assertSame('contact@example.test', $settings->contact_email);
        self::assertStringNotContainsString('contact@example.test', (string) $settings->getRawOriginal('contact_email'));
        $link = $settings->socialLinks()->where('platform', 'github')->firstOrFail();
        self::assertSame('https://github.com/example', $link->url);
        self::assertStringNotContainsString('https://github.com/example', (string) $link->getRawOriginal('url'));

        $private = $reader->forLocale('fr')?->toArray();
        self::assertArrayNotHasKey('email', $private ?? []);
        self::assertArrayNotHasKey('phone', $private ?? []);
        self::assertSame([], $private['social_links'] ?? null);

        $payload = $this->settingsPayload();
        $payload['show_email'] = true;
        $payload['social_links'][0]['is_enabled'] = true;
        $payload['social_links'][0]['is_public'] = true;
        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/settings', $payload)
            ->assertSessionHasNoErrors();

        $public = $reader->forLocale('en')?->toArray();
        self::assertSame('contact@example.test', $public['email'] ?? null);
        self::assertSame('https://github.com/example', $public['social_links']['github'] ?? null);
        self::assertArrayNotHasKey('phone', $public ?? []);

        $auditCount = AuditEvent::query()->where('subject_type', 'site-settings')->count();
        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/settings', $payload)
            ->assertSessionHasNoErrors();
        self::assertSame($auditCount, AuditEvent::query()->where('subject_type', 'site-settings')->count());

        $payload['show_email'] = false;
        $payload['social_links'][0]['is_public'] = false;
        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/settings', $payload)
            ->assertSessionHasNoErrors();
        $privateAgain = $reader->forLocale('fr')?->toArray();
        self::assertArrayNotHasKey('email', $privateAgain ?? []);
        self::assertSame([], $privateAgain['social_links'] ?? null);

        $audit = AuditEvent::query()->where('subject_type', 'site-settings')->get()->toJson();
        self::assertStringNotContainsString('contact@example.test', $audit);
        self::assertStringNotContainsString('https://github.com/example', $audit);
    }

    public function test_invalid_locale_and_unsafe_social_url_are_rejected(): void
    {
        $administrator = User::factory()->administrator()->create();
        $payload = $this->settingsPayload();
        $payload['default_locale'] = 'en';
        $payload['social_links'][0]['url'] = 'javascript:alert(1)';

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/settings', $payload)
            ->assertSessionHasErrors(['default_locale', 'social_links.0.url']);

        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_cv_versions_are_metadata_only_audited_and_absent_from_public_profile(): void
    {
        $administrator = User::factory()->administrator()->create();
        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->put('/dashboard/profile', $this->profilePayload());

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->post('/dashboard/cv-versions', [
                'locale' => 'fr',
                'label' => 'CV français',
                'version_label' => '2026.1',
                'original_filename' => 'cv-2026.pdf',
                'mime_type' => 'application/pdf',
                'size_bytes' => 1024,
                'checksum_sha256' => str_repeat('a', 64),
                'is_verified' => false,
                'published' => false,
                'archived' => false,
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('cv_versions', 1);
        $version = CvVersion::query()->firstOrFail();
        self::assertFalse($version->is_verified);
        self::assertArrayNotHasKey('cv_versions', $this->app->make(PublicProfileReader::class)->forLocale('fr')?->toArray() ?? []);
        self::assertStringNotContainsString('cv-2026.pdf', AuditEvent::query()->get()->toJson());
        $this->get('/cv-2026.pdf')
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=UTF-8')
            ->assertHeaderMissing('content-disposition');

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->post('/dashboard/cv-versions', [
                'locale' => 'en',
                'label' => 'English CV',
                'version_label' => '2026.1',
                'original_filename' => null,
                'mime_type' => null,
                'size_bytes' => null,
                'checksum_sha256' => null,
                'is_verified' => false,
                'published' => false,
                'archived' => false,
            ])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('cv_versions', 2);

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->post('/dashboard/cv-versions', [
                'locale' => 'fr',
                'label' => 'Invalid published CV',
                'version_label' => '2026.2',
                'is_verified' => false,
                'published' => true,
                'archived' => true,
            ])->assertSessionHasErrors(['published', 'archived']);
    }

    public function test_reference_seeding_is_idempotent_and_does_not_create_users_or_public_identity(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('locales', 3);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('profiles', 0);
        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_administrator_privileges_are_not_mass_assignable(): void
    {
        $user = new User;
        $user->fill([
            'name' => 'Safe User',
            'email' => 'safe@example.test',
            'password' => 'password',
            'is_administrator' => true,
            'auth_version' => 99,
        ]);

        self::assertFalse($user->isAdministrator());
        self::assertSame(1, $user->auth_version ?? 1);
    }

    /** @return array<string, mixed> */
    private function profilePayload(): array
    {
        return [
            'display_name' => 'Alaa Khalil',
            'is_published' => true,
            'show_location' => false,
            'show_availability' => false,
            'translations' => [
                'fr' => [
                    'professional_titles' => ['Développeur Laravel'],
                    'summary' => 'Résumé professionnel public.',
                    'location_label' => 'France',
                    'availability' => 'Disponible pour des missions sélectionnées.',
                    'biography' => 'Biographie professionnelle en français.',
                ],
                'en' => [
                    'professional_titles' => ['Laravel Developer'],
                    'summary' => 'English public summary.',
                    'location_label' => 'France',
                    'availability' => 'Available for selected work.',
                    'biography' => 'Professional biography in English.',
                ],
                'ar' => null,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function settingsPayload(): array
    {
        return [
            'site_name' => 'Portfolio professionnel',
            'default_locale' => 'fr',
            'contact_email' => 'contact@example.test',
            'contact_phone' => '+33 6 00 00 00 00',
            'show_email' => false,
            'show_phone' => false,
            'social_links' => [[
                'platform' => 'github',
                'url' => 'https://github.com/example',
                'is_enabled' => false,
                'is_public' => false,
                'sort_order' => 0,
            ]],
            'feature_flags' => [
                'projects' => true,
                'contact' => true,
                'cv' => false,
                'three_d' => false,
            ],
        ];
    }
}
