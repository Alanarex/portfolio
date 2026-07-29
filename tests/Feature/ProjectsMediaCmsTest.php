<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\ActivityLog\Models\AuditEvent;
use Modules\Projects\Application\DeleteMedia;
use Modules\Projects\Application\DeleteProject;
use Modules\Projects\Contracts\MediaStorage;
use Modules\Projects\Contracts\PublicProjectReader;
use Modules\Projects\Infrastructure\LocalMediaStorage;
use Modules\Projects\Models\CaseStudySection;
use Modules\Projects\Models\MediaAsset;
use Modules\Projects\Models\Project;
use RuntimeException;
use Tests\TestCase;

final class ProjectsMediaCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_dashboard_routes_require_the_administrator(): void
    {
        $project = Project::factory()->create();
        $this->get('/dashboard/projects')->assertRedirect('/login');
        $this->get("/dashboard/projects/{$project->id}/preview")->assertRedirect('/login');

        $user = User::factory()->create();
        $this->actingAs($user)
            ->withSession(['auth_version' => $user->auth_version])
            ->get('/dashboard/projects')
            ->assertForbidden();
        $this->actingAs($user)
            ->withSession(['auth_version' => $user->auth_version])
            ->post('/dashboard/projects', $this->projectPayload('forbidden-project'))
            ->assertForbidden();
        $this->actingAs($user)
            ->withSession(['auth_version' => $user->auth_version])
            ->put("/dashboard/projects/{$project->id}", $this->projectPayload('forbidden-update'))
            ->assertForbidden();
        $this->actingAs($user)
            ->withSession(['auth_version' => $user->auth_version])
            ->delete("/dashboard/projects/{$project->id}")
            ->assertForbidden();

        $administrator = User::factory()->administrator()->create();
        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->get('/dashboard/projects')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Index')
                ->has('projects', 1));
        $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version])
            ->get('/dashboard/projects/create')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Edit')
                ->where('isNew', true)
                ->has('project.sections', 8));
    }

    public function test_projects_can_be_created_filtered_reordered_previewed_and_deleted(): void
    {
        $administrator = User::factory()->administrator()->create();
        $this->asAdministrator($administrator)
            ->post('/dashboard/projects', $this->projectPayload('alpha-project'))
            ->assertSessionHasNoErrors();
        $this->asAdministrator($administrator)
            ->post('/dashboard/projects', $this->projectPayload('beta-project', false))
            ->assertSessionHasNoErrors();

        $alpha = Project::query()->where('slug', 'alpha-project')->firstOrFail();
        $beta = Project::query()->where('slug', 'beta-project')->firstOrFail();
        $this->assertDatabaseCount('projects', 2);
        $this->assertDatabaseCount('case_study_sections', 2);

        $this->asAdministrator($administrator)
            ->get('/dashboard/projects?status=published&featured=yes')
            ->assertInertia(fn (Assert $page) => $page
                ->has('projects', 1)
                ->where('projects.0.slug', 'alpha-project'));
        $this->asAdministrator($administrator)
            ->get("/dashboard/projects/{$alpha->id}/preview")
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Preview')
                ->where('project.slug', 'alpha-project')
                ->where('project.sections.0.type', 'context'));

        $this->asAdministrator($administrator)
            ->put('/dashboard/projects/order', ['ids' => [$beta->id, $alpha->id]])
            ->assertSessionHasNoErrors();
        self::assertSame(10, $beta->fresh()?->sort_order);
        self::assertSame(20, $alpha->fresh()?->sort_order);

        $this->asAdministrator($administrator)
            ->delete("/dashboard/projects/{$beta->id}")
            ->assertRedirect('/dashboard/projects');
        $this->assertDatabaseMissing('projects', ['id' => $beta->id]);
        self::assertSame(
            [
                'project.created',
                'project.created',
                'projects.reordered',
                'project.deletion-requested',
                'project.deleted',
            ],
            AuditEvent::query()->pluck('action')->all(),
        );
    }

    public function test_publication_requires_verified_bilingual_content_and_stable_slugs(): void
    {
        $administrator = User::factory()->administrator()->create();
        $invalid = $this->projectPayload('invalid-project');
        $invalid['translations']['en']['summary'] = '';
        $invalid['sections'][0]['is_verified'] = false;

        $this->asAdministrator($administrator)
            ->post('/dashboard/projects', $invalid)
            ->assertSessionHasErrors([
                'translations.en',
                'sections.0.is_verified',
            ]);
        $this->assertDatabaseCount('projects', 0);

        $this->asAdministrator($administrator)
            ->post('/dashboard/projects', $this->projectPayload('stable-project'))
            ->assertSessionHasNoErrors();
        $project = Project::query()->firstOrFail();
        $payload = $this->projectPayload('changed-project');
        $this->asAdministrator($administrator)
            ->put("/dashboard/projects/{$project->id}", $payload)
            ->assertSessionHasErrors('slug');
        self::assertSame('stable-project', $project->fresh()?->slug);
    }

    public function test_case_study_section_ids_cannot_be_moved_between_projects(): void
    {
        $administrator = User::factory()->administrator()->create();
        $this->asAdministrator($administrator)
            ->post('/dashboard/projects', $this->projectPayload('first-owner'))
            ->assertSessionHasNoErrors();
        $this->asAdministrator($administrator)
            ->post('/dashboard/projects', $this->projectPayload('second-owner'))
            ->assertSessionHasNoErrors();

        $first = Project::query()->where('slug', 'first-owner')->firstOrFail();
        $second = Project::query()->where('slug', 'second-owner')->firstOrFail();
        $foreignSection = $second->caseStudySections()->firstOrFail();
        $payload = $this->projectPayload('first-owner');
        $payload['sections'][0]['id'] = $foreignSection->id;
        $payload['translations']['fr']['title'] = 'Titre qui ne doit pas être enregistré';

        $this->asAdministrator($administrator)
            ->put("/dashboard/projects/{$first->id}", $payload)
            ->assertNotFound();

        self::assertNotSame(
            'Titre qui ne doit pas être enregistré',
            $first->translations()->where('locale', 'fr')->firstOrFail()->fresh()?->title,
        );
        self::assertSame($second->id, $foreignSection->fresh()?->project_id);
        self::assertSame(1, $first->caseStudySections()->count());
    }

    public function test_private_repository_metadata_is_encrypted_and_never_public(): void
    {
        $administrator = User::factory()->administrator()->create();
        $payload = $this->projectPayload('private-repository');
        $payload['repository_visibility'] = 'private';
        $payload['repository_url'] = 'https://github.com/example/private-repository';
        $payload['show_repository'] = false;

        $this->asAdministrator($administrator)->post('/dashboard/projects', $payload)->assertSessionHasNoErrors();
        $project = Project::query()->firstOrFail();
        self::assertSame('https://github.com/example/private-repository', $project->repository_url);
        self::assertStringNotContainsString(
            'github.com/example/private-repository',
            (string) $project->getRawOriginal('repository_url'),
        );

        $public = $this->app->make(PublicProjectReader::class)->findBySlug('fr', 'private-repository')?->toArray();
        self::assertArrayNotHasKey('repository_url', $public ?? []);
        self::assertArrayNotHasKey('source_reference', $public ?? []);
        self::assertArrayNotHasKey('id', $public ?? []);
        self::assertStringNotContainsString(
            'github.com/example/private-repository',
            AuditEvent::query()->get()->toJson(),
        );
    }

    public function test_public_reader_filters_orders_localizes_and_invalidates_cache(): void
    {
        $administrator = User::factory()->administrator()->create();
        $first = $this->projectPayload('first-project');
        $first['featured_order'] = 20;
        $second = $this->projectPayload('second-project');
        $second['featured_order'] = 10;
        $draft = $this->projectPayload('draft-project', false);
        $draft['publication_status'] = 'draft';
        foreach ([$first, $second, $draft] as $payload) {
            $this->asAdministrator($administrator)->post('/dashboard/projects', $payload)->assertSessionHasNoErrors();
        }

        $reader = $this->app->make(PublicProjectReader::class);
        self::assertSame(
            ['second-project', 'first-project'],
            array_map(fn ($project): string => $project->slug, $reader->featured('fr')),
        );
        self::assertSame('Second project', $reader->findBySlug('en', 'second-project')?->title);
        self::assertNull($reader->findBySlug('ar', 'second-project'));
        self::assertNull($reader->findBySlug('fr', 'draft-project'));

        $secondProject = Project::query()->where('slug', 'second-project')->firstOrFail();
        $updated = $second;
        $updated['translations']['fr']['title'] = 'Projet mis à jour';
        $this->asAdministrator($administrator)
            ->put("/dashboard/projects/{$secondProject->id}", $updated)
            ->assertSessionHasNoErrors();
        self::assertSame('Projet mis à jour', $reader->findBySlug('fr', 'second-project')?->title);
    }

    public function test_media_uploads_enforce_content_extension_size_authorization_and_public_metadata(): void
    {
        Storage::fake('local');
        $administrator = User::factory()->administrator()->create();
        $this->asAdministrator($administrator)
            ->post('/dashboard/projects', $this->projectPayload('media-project'))
            ->assertSessionHasNoErrors();
        $project = Project::query()->firstOrFail();

        $user = User::factory()->create();
        $this->actingAs($user)
            ->withSession(['auth_version' => $user->auth_version])
            ->post('/dashboard/media', $this->mediaPayload($project, $this->png('private-name.png')))
            ->assertForbidden();

        $this->asAdministrator($administrator)
            ->post('/dashboard/media', $this->mediaPayload($project, $this->png('private-name.png')))
            ->assertSessionHasNoErrors();
        $asset = MediaAsset::query()->firstOrFail();
        Storage::disk('local')->assertExists($asset->path);
        self::assertStringNotContainsString('private-name.png', $asset->path);
        self::assertStringNotContainsString('private-name.png', (string) $asset->getRawOriginal('original_filename'));
        self::assertStringNotContainsString($asset->path, (string) $asset->getRawOriginal('path'));
        self::assertSame(1, $asset->width);
        self::assertSame(1, $asset->height);

        $public = $this->app->make(PublicProjectReader::class)->findBySlug('fr', 'media-project')?->toArray();
        self::assertSame($asset->uuid, $public['media'][0]['delivery_key'] ?? null);
        self::assertArrayNotHasKey('path', $public['media'][0] ?? []);
        self::assertArrayNotHasKey('disk', $public['media'][0] ?? []);
        self::assertArrayNotHasKey('checksum_sha256', $public['media'][0] ?? []);
        self::assertArrayNotHasKey('original_filename', $public['media'][0] ?? []);

        $spoofed = UploadedFile::fake()->createWithContent('shell.jpg', '<?php echo "unsafe";');
        $this->asAdministrator($administrator)
            ->post('/dashboard/media', $this->mediaPayload($project, $spoofed))
            ->assertSessionHasErrors('file');

        $truncated = UploadedFile::fake()->createWithContent(
            'truncated.png',
            substr($this->pngContents(), 0, 33),
        );
        $this->asAdministrator($administrator)
            ->post('/dashboard/media', $this->mediaPayload($project, $truncated))
            ->assertSessionHasErrors('file');

        $hugeHeader = substr_replace($this->pngContents(), pack('N', 20_000), 16, 4);
        $huge = UploadedFile::fake()->createWithContent('huge.png', $hugeHeader);
        $this->asAdministrator($administrator)
            ->post('/dashboard/media', $this->mediaPayload($project, $huge))
            ->assertSessionHasErrors('file');

        $overPixelBudget = substr_replace($this->pngContents(), pack('N', 5000).pack('N', 3000), 16, 8);
        $this->asAdministrator($administrator)
            ->post(
                '/dashboard/media',
                $this->mediaPayload(
                    $project,
                    UploadedFile::fake()->createWithContent('pixel-budget.png', $overPixelBudget),
                ),
            )
            ->assertSessionHasErrors('file');

        $oversized = UploadedFile::fake()->create('large.pdf', 20 * 1024 + 1, 'application/pdf');
        $payload = $this->mediaPayload($project, $oversized);
        $payload['kind'] = 'document';
        $this->asAdministrator($administrator)
            ->post('/dashboard/media', $payload)
            ->assertSessionHasErrors('file');
        $this->assertDatabaseCount('media_assets', 1);

        $this->asAdministrator($administrator)
            ->put("/dashboard/media/{$asset->id}", [
                'is_public' => false,
                'sort_order' => 20,
                'translations' => [
                    'fr' => ['alt_text' => 'Capture mise à jour', 'caption' => 'Vue privée'],
                    'en' => ['alt_text' => 'Updated screenshot', 'caption' => 'Private view'],
                ],
            ])
            ->assertSessionHasNoErrors();
        self::assertFalse($asset->fresh()?->is_public);
        self::assertSame(
            [],
            $this->app->make(PublicProjectReader::class)->findBySlug('fr', 'media-project')?->media,
        );

        $storedPath = $asset->path;
        $this->asAdministrator($administrator)
            ->delete("/dashboard/media/{$asset->id}")
            ->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($storedPath);
        $this->assertDatabaseCount('media_assets', 0);
    }

    public function test_storage_deletion_failures_remain_private_and_can_be_retried(): void
    {
        Storage::fake('local');
        $administrator = User::factory()->administrator()->create();
        $this->asAdministrator($administrator)
            ->post('/dashboard/projects', $this->projectPayload('retry-deletion'))
            ->assertSessionHasNoErrors();
        $project = Project::query()->firstOrFail();
        $this->asAdministrator($administrator)
            ->post('/dashboard/media', $this->mediaPayload($project, $this->png('retry.png')))
            ->assertSessionHasNoErrors();
        $asset = MediaAsset::query()->firstOrFail();
        $reader = $this->app->make(PublicProjectReader::class);
        self::assertCount(1, $reader->findBySlug('fr', $project->slug)?->media ?? []);

        $failingMediaStorage = \Mockery::mock(MediaStorage::class);
        $failingMediaStorage->shouldReceive('delete')
            ->once()
            ->with($asset->disk, $asset->path)
            ->andThrow(new RuntimeException('simulated media storage failure'));
        $this->app->instance(MediaStorage::class, $failingMediaStorage);
        try {
            $this->app->make(DeleteMedia::class)->execute($asset, $administrator, null);
            self::fail('The media deletion should surface the storage failure.');
        } catch (RuntimeException $exception) {
            self::assertSame('simulated media storage failure', $exception->getMessage());
        }

        self::assertNotNull($asset->fresh()?->deletion_pending_at);
        self::assertSame([], $reader->findBySlug('fr', $project->slug)?->media);
        Storage::disk('local')->assertExists($asset->path);

        $this->restoreLocalMediaStorage();
        $this->app->make(DeleteMedia::class)->execute($asset, $administrator, null);
        self::assertNull($asset->fresh());
        Storage::disk('local')->assertMissing($asset->path);

        $this->asAdministrator($administrator)
            ->post('/dashboard/media', $this->mediaPayload($project, $this->png('project-retry.png')))
            ->assertSessionHasNoErrors();
        $projectAsset = MediaAsset::query()->firstOrFail();
        $failingProjectStorage = \Mockery::mock(MediaStorage::class);
        $failingProjectStorage->shouldReceive('delete')
            ->once()
            ->with($projectAsset->disk, $projectAsset->path)
            ->andThrow(new RuntimeException('simulated project storage failure'));
        $this->app->instance(MediaStorage::class, $failingProjectStorage);
        try {
            $this->app->make(DeleteProject::class)->execute($project, $administrator, null);
            self::fail('The project deletion should surface the storage failure.');
        } catch (RuntimeException $exception) {
            self::assertSame('simulated project storage failure', $exception->getMessage());
        }

        self::assertNotNull($project->fresh()?->deletion_pending_at);
        self::assertNotNull($projectAsset->fresh()?->deletion_pending_at);
        self::assertNull($reader->findBySlug('fr', $project->slug));
        Storage::disk('local')->assertExists($projectAsset->path);

        $guardedStorage = \Mockery::mock(MediaStorage::class);
        $guardedStorage->shouldNotReceive('store');
        $this->app->instance(MediaStorage::class, $guardedStorage);
        $this->asAdministrator($administrator)
            ->post(
                '/dashboard/media',
                $this->mediaPayload($project, $this->png('concurrent-upload.png')),
            )
            ->assertSessionHasErrors('project_id');

        $this->restoreLocalMediaStorage();
        $this->app->make(DeleteProject::class)->execute($project, $administrator, null);
        self::assertNull($project->fresh());
        self::assertNull($projectAsset->fresh());
        Storage::disk('local')->assertMissing($projectAsset->path);
    }

    public function test_project_deletion_cascades_media_and_removes_private_files(): void
    {
        Storage::fake('local');
        $administrator = User::factory()->administrator()->create();
        $this->asAdministrator($administrator)
            ->post('/dashboard/projects', $this->projectPayload('delete-media-project'))
            ->assertSessionHasNoErrors();
        $project = Project::query()->firstOrFail();
        $this->asAdministrator($administrator)
            ->post('/dashboard/media', $this->mediaPayload($project, $this->png('project.png')))
            ->assertSessionHasNoErrors();
        $asset = MediaAsset::query()->firstOrFail();
        $path = $asset->path;

        $this->asAdministrator($administrator)
            ->delete("/dashboard/projects/{$project->id}")
            ->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('media_assets', 0);
    }

    public function test_verified_project_seed_is_idempotent_draft_only_and_does_not_overwrite_edits(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('projects', 7);
        $this->assertDatabaseCount('project_translations', 7);
        $this->assertDatabaseCount('case_study_sections', 1);
        $this->assertDatabaseMissing('projects', ['publication_status' => 'published']);
        self::assertSame(0, Project::query()->whereNotNull('repository_url')->count());

        $project = Project::query()->where('slug', 'gm-exchange')->firstOrFail();
        $translation = $project->translations()->where('locale', 'fr')->firstOrFail();
        $translation->summary = 'Modification administrateur conservée.';
        $translation->save();
        $this->seed(DatabaseSeeder::class);
        self::assertSame('Modification administrateur conservée.', $translation->fresh()?->summary);

        $metrics = CaseStudySection::query()->firstOrFail();
        self::assertTrue($metrics->is_verified);
        self::assertFalse($metrics->is_public);
        self::assertNull($this->app->make(PublicProjectReader::class)->findBySlug('fr', 'handicapacite'));
    }

    private function asAdministrator(User $administrator)
    {
        return $this->actingAs($administrator)
            ->withSession(['auth_version' => $administrator->auth_version]);
    }

    /** @return array<string, mixed> */
    private function projectPayload(string $slug, bool $featured = true): array
    {
        return [
            'slug' => $slug,
            'lifecycle_status' => 'completed',
            'technologies' => ['Laravel', 'PHP'],
            'start_year' => 2025,
            'start_month' => 1,
            'end_year' => 2026,
            'end_month' => 6,
            'is_ongoing' => false,
            'repository_visibility' => 'public',
            'repository_url' => "https://github.com/example/{$slug}",
            'show_repository' => true,
            'demo_url' => "https://example.test/{$slug}",
            'show_demo' => true,
            'publication_status' => 'published',
            'is_featured' => $featured,
            'featured_order' => $featured ? 10 : null,
            'sort_order' => 10,
            'translations' => [
                'fr' => [
                    'title' => 'Projet '.str_replace('-', ' ', $slug),
                    'summary' => 'Résumé public vérifié.',
                    'role' => 'Développeur principal',
                    'seo_title' => '',
                    'seo_description' => '',
                ],
                'en' => [
                    'title' => ucfirst(str_replace('-', ' ', $slug)),
                    'summary' => 'Verified public summary.',
                    'role' => 'Lead developer',
                    'seo_title' => '',
                    'seo_description' => '',
                ],
            ],
            'sections' => [[
                'type' => 'context',
                'is_public' => true,
                'is_verified' => true,
                'sort_order' => 10,
                'translations' => [
                    'fr' => ['heading' => 'Contexte', 'body' => 'Contexte vérifié du projet.'],
                    'en' => ['heading' => 'Context', 'body' => 'Verified project context.'],
                ],
            ]],
        ];
    }

    /** @return array<string, mixed> */
    private function mediaPayload(Project $project, UploadedFile $file): array
    {
        return [
            'project_id' => $project->id,
            'kind' => 'image',
            'file' => $file,
            'is_public' => true,
            'sort_order' => 10,
            'translations' => [
                'fr' => ['alt_text' => 'Capture du projet', 'caption' => 'Vue principale'],
                'en' => ['alt_text' => 'Project screenshot', 'caption' => 'Main view'],
            ],
        ];
    }

    private function png(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $this->pngContents());
    }

    private function pngContents(): string
    {
        $content = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2n8sAAAAASUVORK5CYII=',
            true,
        );
        self::assertIsString($content);

        return $content;
    }

    private function restoreLocalMediaStorage(): void
    {
        $this->app->forgetInstance(MediaStorage::class);
        $this->app->bind(MediaStorage::class, LocalMediaStorage::class);
    }
}
