<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\ActivityLog\Models\AuditEvent;
use Modules\Contact\Mail\ContactMessageMail;
use Modules\Profile\Application\SaveCvVersion;
use Modules\Profile\Models\CvVersion;
use Modules\Profile\Models\Profile;
use Modules\Projects\Models\Project;
use Modules\Settings\Models\SiteSetting;
use Tests\TestCase;

final class ContactSeoCvTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_queues_mail_without_exposing_recipient_or_persisting_messages(): void
    {
        Mail::fake();
        $this->publishedProfile();
        $settings = $this->settings(contact: true);

        $this->get('/fr')
            ->assertOk()
            ->assertSee('action="'.route('portfolio.contact.submit', ['locale' => 'fr']).'"', false)
            ->assertDontSee('private-recipient@example.test');

        $this->post('/fr/contact', [
            'name' => 'Camille Martin',
            'email' => 'camille@example.test',
            'subject' => 'Projet Laravel',
            'message' => 'Bonjour, je souhaite discuter d’un projet Laravel.',
            'website' => '',
        ])->assertRedirect()->assertSessionHas('contact_success');

        Mail::assertQueued(ContactMessageMail::class, function (ContactMessageMail $mail) use ($settings): bool {
            return $mail->hasTo((string) $settings->contact_email)
                && $mail->submission->email === 'camille@example.test'
                && $mail->submission->locale === 'fr';
        });
        self::assertFalse(Schema::hasTable('contact_messages'));
    }

    public function test_contact_spam_controls_are_accessible_and_header_injection_is_rejected(): void
    {
        Mail::fake();
        $this->settings(contact: true);

        $this->post('/fr/contact', [
            'name' => "Attacker\r\nBcc: victim@example.test",
            'email' => 'attacker@example.test',
            'message' => 'This message is long enough to pass the length rule.',
            'website' => '',
        ])->assertSessionHasErrors('name');

        $this->post('/fr/contact', [
            'name' => 'Robot',
            'email' => 'robot@example.test',
            'message' => 'This automated message is long enough.',
            'website' => 'https://spam.example',
        ])->assertRedirect()->assertSessionHas('contact_success');

        Mail::assertNothingQueued();
    }

    public function test_contact_endpoint_is_disabled_without_configuration_and_rate_limited(): void
    {
        Mail::fake();
        $this->settings(contact: false);
        $payload = [
            'name' => 'Camille',
            'email' => 'camille@example.test',
            'message' => 'Bonjour, voici un message de test suffisant.',
            'website' => '',
        ];

        $this->post('/fr/contact', $payload)->assertNotFound();

        $settings = SiteSetting::query()->where('key', 'main')->firstOrFail();
        $settings->featureFlags()->where('key', 'contact')->update(['enabled' => true]);

        $this->post('/fr/contact', $payload)->assertRedirect();
        $this->post('/fr/contact', $payload)->assertRedirect();
        $this->post('/fr/contact', $payload)->assertTooManyRequests();
    }

    public function test_verified_published_cv_is_privately_stored_and_securely_delivered(): void
    {
        Storage::fake('cv');
        $profile = $this->publishedProfile();
        $this->settings(cv: true);
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->post('/dashboard/cv-versions', [
                'locale' => 'fr',
                'label' => 'CV français',
                'version_label' => '2026.1',
                'document' => $this->pdf(),
                'is_verified' => true,
                'published' => true,
                'archived' => false,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $version = CvVersion::query()->where('profile_id', $profile->id)->firstOrFail();
        self::assertNotNull($version->path);
        self::assertNotNull($version->checksum_sha256);
        self::assertSame('cv', $version->disk);
        Storage::disk('cv')->assertExists((string) $version->path);

        $this->get('/fr/cv')
            ->assertOk()
            ->assertDownload('cv-fr-2026.1.pdf')
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Cache-Control', 'no-store, private');

        Storage::disk('cv')->put((string) $version->path, 'tampered');
        $this->get('/fr/cv')->assertNotFound();
    }

    public function test_publishing_a_new_cv_unpublishes_and_audits_the_previous_locale_version(): void
    {
        Storage::fake('cv');
        $profile = $this->publishedProfile();
        $this->settings(cv: true);
        $administrator = User::factory()->administrator()->create();
        $this->actingAs($administrator)->withSession(['auth_version' => $administrator->auth_version]);

        foreach (['2026.1', '2026.2'] as $versionLabel) {
            $this->post('/dashboard/cv-versions', [
                'locale' => 'fr',
                'label' => 'CV français '.$versionLabel,
                'version_label' => $versionLabel,
                'document' => $this->pdf(),
                'is_verified' => true,
                'published' => true,
                'archived' => false,
            ])->assertRedirect()->assertSessionHasNoErrors();
        }

        $versions = CvVersion::query()
            ->where('profile_id', $profile->id)
            ->orderBy('version_label')
            ->get();

        self::assertNull($versions[0]->published_at);
        self::assertNotNull($versions[1]->published_at);
        self::assertSame(1, CvVersion::query()->whereNotNull('published_at')->count());
        self::assertTrue(AuditEvent::query()
            ->where('action', 'cv-version.unpublished')
            ->where('subject_id', (string) $versions[0]->id)
            ->exists());
    }

    public function test_cv_delivery_requires_every_publication_and_privacy_gate(): void
    {
        Storage::fake('cv');
        $this->publishedProfile();
        $this->settings(cv: false);
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->post('/dashboard/cv-versions', [
                'locale' => 'fr',
                'label' => 'CV français',
                'version_label' => '2026.1',
                'document' => $this->pdf(),
                'is_verified' => true,
                'published' => true,
                'archived' => false,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->get('/fr/cv')->assertNotFound();
        $this->get('/en/cv')->assertNotFound();
    }

    public function test_cv_upload_rejects_a_public_filesystem_disk(): void
    {
        Storage::fake('public');
        config()->set('profile.cv_disk', 'public');
        $this->publishedProfile();
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->post('/dashboard/cv-versions', [
                'locale' => 'fr',
                'label' => 'CV français',
                'version_label' => '2026.1',
                'document' => $this->pdf(),
                'is_verified' => true,
                'published' => true,
                'archived' => false,
            ])
            ->assertServerError();

        self::assertSame(0, CvVersion::query()->count());
    }

    public function test_stale_cv_update_cannot_resurrect_a_concurrently_deleted_version_or_orphan_upload(): void
    {
        Storage::fake('cv');
        $profile = $this->publishedProfile();
        $administrator = User::factory()->administrator()->create();
        $staleVersion = CvVersion::factory()->create([
            'profile_id' => $profile->id,
            'locale' => 'fr',
            'version_label' => '2026.1',
        ]);
        CvVersion::query()->whereKey($staleVersion->id)->delete();

        try {
            app(SaveCvVersion::class)->execute([
                'locale' => 'fr',
                'label' => 'CV français',
                'version_label' => '2026.2',
                'document' => $this->pdf(),
                'is_verified' => true,
                'published' => true,
                'archived' => false,
            ], $administrator, 'concurrent-delete-test', $staleVersion);
            self::fail('A stale update must not recreate a concurrently deleted CV version.');
        } catch (ModelNotFoundException) {
            self::assertSame(0, CvVersion::query()->count());
            self::assertSame([], Storage::disk('cv')->allFiles());
        }
    }

    public function test_sitemap_robots_metadata_privacy_and_error_pages_are_safe(): void
    {
        $this->publishedProfile();
        $this->settings(activity: false);
        $project = Project::factory()->create([
            'slug' => 'public-project',
            'publication_status' => 'published',
            'published_at' => now(),
        ]);
        $project->translations()->createMany([
            ['locale' => 'fr', 'title' => 'Projet public', 'summary' => 'Résumé public', 'role' => 'Développeur', 'seo_title' => null, 'seo_description' => null],
            ['locale' => 'en', 'title' => 'Public project', 'summary' => 'Public summary', 'role' => 'Developer', 'seo_title' => null, 'seo_description' => null],
        ]);
        $draft = Project::factory()->create(['slug' => 'private-draft', 'publication_status' => 'draft']);
        $draft->translations()->create(['locale' => 'fr', 'title' => 'Brouillon', 'summary' => 'Privé', 'role' => 'Développeur', 'seo_title' => null, 'seo_description' => null]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('/fr/projects/public-project', false)
            ->assertSee('/en/projects/public-project', false)
            ->assertDontSee('private-draft');

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /dashboard')
            ->assertSee(route('portfolio.sitemap'));

        $this->get('/fr')
            ->assertOk()
            ->assertSee('rel="canonical"', false)
            ->assertSee('name="twitter:card"', false)
            ->assertSee('type="application/ld+json"', false)
            ->assertSee('"@context":"https://schema.org"', false)
            ->assertDontSee('Activité GitHub &amp; GitLab', false);

        $this->get('/fr/privacy')
            ->assertOk()
            ->assertSee('Aucun fournisseur d’analytics');

        $this->get('/fr/projects/not-found')
            ->assertNotFound()
            ->assertSee('Page introuvable')
            ->assertDontSee('Stack trace');
    }

    private function publishedProfile(): Profile
    {
        $profile = Profile::factory()->create([
            'key' => 'main',
            'display_name' => 'Alaa Khalil',
            'is_published' => true,
            'published_at' => now(),
        ]);
        $profile->translations()->createMany([
            [
                'locale' => 'fr',
                'professional_titles' => ['Développeur Laravel'],
                'summary' => 'Résumé public.',
                'location_label' => null,
                'availability' => null,
                'biography' => 'Biographie publique.',
            ],
            [
                'locale' => 'en',
                'professional_titles' => ['Laravel Developer'],
                'summary' => 'Public summary.',
                'location_label' => null,
                'availability' => null,
                'biography' => 'Public biography.',
            ],
        ]);

        return $profile;
    }

    private function settings(
        bool $contact = false,
        bool $cv = false,
        bool $activity = false,
    ): SiteSetting {
        $settings = SiteSetting::query()->where('key', 'main')->first();
        if ($settings === null) {
            $settings = SiteSetting::factory()->create([
                'key' => 'main',
                'site_name' => 'Alaa Khalil',
                'default_locale' => 'fr',
                'contact_email' => 'private-recipient@example.test',
                'show_email' => false,
                'show_phone' => false,
            ]);
        }

        foreach ([
            'projects' => true,
            'contact' => $contact,
            'cv' => $cv,
            'activity' => $activity,
            'three_d' => false,
        ] as $key => $enabled) {
            $settings->featureFlags()->updateOrCreate(['key' => $key], ['enabled' => $enabled]);
        }

        return $settings->refresh();
    }

    private function pdf(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'cv.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF",
        );
    }
}
