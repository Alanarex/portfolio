<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Career\Contracts\PublicCareerReader;
use Modules\Career\Data\PublicCareerData;
use Modules\Profile\Contracts\PublicProfileReader;
use Modules\Profile\Data\PublicProfileData;
use Modules\Projects\Contracts\PublicProjectReader;
use Modules\Projects\Data\PublicProjectData;
use Modules\Projects\Infrastructure\DatabasePublicProjectReader;
use Modules\Projects\Models\MediaAsset;
use Modules\Projects\Models\Project;
use Modules\Settings\Contracts\PublicSettingsReader;
use Modules\Settings\Data\PublicSettingsData;
use Modules\Skills\Contracts\PublicSkillsReader;
use Modules\Skills\Data\PublicSkillsData;
use Tests\TestCase;

final class PublicPortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_french_is_the_x_default_and_the_landing_page_is_html_first(): void
    {
        $project = $this->projectData('portfolio', 'Portfolio V3');
        $this->bindReaders([$project]);

        $this->get('/')->assertStatus(301)->assertRedirect('/fr');

        $response = $this->get('/fr')
            ->assertOk()
            ->assertSee('<html lang="fr"', false)
            ->assertSee('Alaa Khalil')
            ->assertSee('Développeur PHP / Laravel')
            ->assertSee('Portfolio V3')
            ->assertSee('385 requêtes SQL à 6')
            ->assertSee('Scène interactive en préparation')
            ->assertSee('Aucune activité privée n’est exposée')
            ->assertSeeInOrder([
                'Positionnement',
                'Projets à la une',
                'Résultats vérifiés',
                'Espace développeur 3D',
                'Forces techniques',
                'Expérience',
                'Compétences et stack',
                'Activité GitHub & GitLab',
                'Formation et certifications',
                'En dehors du code',
                'Construisons quelque chose d’utile',
            ]);

        self::assertStringNotContainsString('id="portfolio-app"', $response->getContent());
        self::assertStringNotContainsString('Alex Karim', $response->getContent());
    }

    public function test_localized_project_index_and_stable_case_study_routes_render_public_data(): void
    {
        $project = $this->projectData('stable-project', 'Projet stable');
        $this->bindReaders([$project]);

        $this->get('/fr/projects')
            ->assertOk()
            ->assertSee('Projet stable')
            ->assertSee('/fr/projects/stable-project', false)
            ->assertSee('Laravel');

        $this->get('/fr/projects/stable-project')
            ->assertOk()
            ->assertSee('<h1>Projet stable</h1>', false)
            ->assertSee('Contexte vérifié')
            ->assertSee('Code source')
            ->assertSee('Voir la démo');

        $this->get('/fr/projects/missing-project')->assertNotFound();
        $this->get('/de/projects')->assertNotFound();
    }

    public function test_database_reader_keeps_drafts_and_incomplete_translations_out_of_public_pages(): void
    {
        $published = $this->storedProject('published-project', 'published', ['fr', 'en']);
        $this->storedProject('draft-project', 'draft', ['fr', 'en']);
        $this->storedProject('french-only-project', 'published', ['fr']);
        Cache::clear();
        $this->bindSupportingReaders();
        $this->app->instance(PublicProjectReader::class, new DatabasePublicProjectReader);

        $this->get('/fr/projects')
            ->assertOk()
            ->assertSee($published->slug)
            ->assertSee('french-only-project')
            ->assertDontSee('draft-project');

        $this->get('/en/projects')
            ->assertOk()
            ->assertSee($published->slug)
            ->assertDontSee('french-only-project')
            ->assertDontSee('draft-project');

        $this->get('/fr/projects/draft-project')->assertNotFound();
        $this->get('/en/projects/french-only-project')->assertNotFound();
    }

    public function test_media_delivery_rechecks_project_publication_locale_and_asset_visibility(): void
    {
        Storage::fake('local');
        $project = $this->storedProject('media-project', 'published', ['fr']);
        $asset = MediaAsset::query()->create([
            'uuid' => (string) Str::uuid(),
            'project_id' => $project->id,
            'kind' => 'image',
            'disk' => 'local',
            'path' => 'projects/media-project/cover.png',
            'original_filename' => 'private-source-name.png',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'size_bytes' => 68,
            'checksum_sha256' => str_repeat('a', 64),
            'width' => 1,
            'height' => 1,
            'is_public' => true,
            'sort_order' => 10,
        ]);
        $asset->translations()->create(['locale' => 'fr', 'alt_text' => 'Capture publique', 'caption' => null]);
        Storage::disk('local')->put($asset->path, $this->pngContents());

        $this->get("/fr/media/{$asset->uuid}")
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Cache-Control');
        $this->get("/en/media/{$asset->uuid}")->assertNotFound();

        $asset->is_public = false;
        $asset->save();
        $this->get("/fr/media/{$asset->uuid}")->assertNotFound();

        $asset->is_public = true;
        $asset->save();
        $project->publication_status = 'draft';
        $project->save();
        $this->get("/fr/media/{$asset->uuid}")->assertNotFound();
    }

    /** @param list<PublicProjectData> $projects */
    private function bindReaders(array $projects): void
    {
        $this->bindSupportingReaders();
        $this->app->instance(PublicProjectReader::class, new class($projects) implements PublicProjectReader
        {
            /** @param list<PublicProjectData> $projects */
            public function __construct(private readonly array $projects) {}

            public function all(string $locale): array
            {
                return $this->projects;
            }

            public function featured(string $locale): array
            {
                return $this->projects;
            }

            public function findBySlug(string $locale, string $slug): ?PublicProjectData
            {
                foreach ($this->projects as $project) {
                    if ($project->slug === $slug) {
                        return $project;
                    }
                }

                return null;
            }
        });
    }

    private function bindSupportingReaders(): void
    {
        $this->app->instance(PublicProfileReader::class, new class implements PublicProfileReader
        {
            public function forLocale(string $locale): ?PublicProfileData
            {
                return new PublicProfileData(
                    displayName: 'Alaa Khalil',
                    professionalTitles: ['Développeur PHP / Laravel', 'Pilotage technique'],
                    summary: 'Je conçois des applications web robustes et utiles.',
                    biography: 'Un parcours full-stack orienté produit, qualité et mise en production.',
                    location: 'France',
                    availability: 'Disponible pour échanger',
                );
            }
        });
        $this->app->instance(PublicSettingsReader::class, new class implements PublicSettingsReader
        {
            public function forLocale(string $locale): ?PublicSettingsData
            {
                return new PublicSettingsData(
                    siteName: 'Alaa Khalil',
                    locale: $locale,
                    email: 'contact@example.test',
                    phone: null,
                    socialLinks: ['github' => 'https://github.com/example'],
                    featureFlags: ['activity' => true],
                    contactFormEnabled: true,
                );
            }
        });
        $this->app->instance(PublicCareerReader::class, new class implements PublicCareerReader
        {
            public function forLocale(string $locale): ?PublicCareerData
            {
                return new PublicCareerData(
                    experiences: [[
                        'key' => 'cosika',
                        'organization' => 'COSIKA',
                        'role' => 'Développeur PHP / Laravel',
                        'employment_type' => 'Alternance',
                        'location' => 'France',
                        'summary' => 'Applications métier et API.',
                        'highlights' => ['Laravel', 'Vue.js', 'Qualité'],
                        'start' => ['year' => 2024, 'month' => 9],
                        'end' => null,
                        'is_current' => true,
                        'achievements' => [[
                            'key' => 'sql',
                            'statement' => 'Un cas mesuré est passé de 385 requêtes SQL à 6.',
                            'is_quantified' => true,
                        ]],
                    ]],
                    education: [[
                        'key' => 'school',
                        'institution' => 'MyDigitalSchool',
                        'program' => 'Manager de Projet Web et Digital',
                        'level' => 'RNCP 7',
                        'location' => 'Angers',
                        'summary' => null,
                        'start' => ['year' => 2024, 'month' => null],
                        'end' => ['year' => 2026, 'month' => null],
                    ]],
                    certifications: [],
                    languages: [],
                );
            }
        });
        $this->app->instance(PublicSkillsReader::class, new class implements PublicSkillsReader
        {
            public function forLocale(string $locale): ?PublicSkillsData
            {
                return new PublicSkillsData([[
                    'key' => 'backend',
                    'name' => 'Backend',
                    'skills' => [
                        ['key' => 'php', 'name' => 'PHP', 'description' => null],
                        ['key' => 'laravel', 'name' => 'Laravel', 'description' => null],
                    ],
                ]]);
            }
        });
    }

    private function projectData(string $slug, string $title): PublicProjectData
    {
        return new PublicProjectData(
            slug: $slug,
            title: $title,
            summary: 'Une réalisation publique vérifiée.',
            role: 'Développeur full-stack',
            lifecycleStatus: 'maintained',
            technologies: ['Laravel', 'PHP', 'PostgreSQL'],
            dates: [
                'start' => ['year' => 2025, 'month' => 1],
                'end' => ['year' => 2026, 'month' => 6],
                'ongoing' => false,
            ],
            featured: true,
            repositoryUrl: 'https://github.com/example/project',
            demoUrl: 'https://example.test',
            sections: [[
                'type' => 'context',
                'heading' => 'Contexte vérifié',
                'body' => 'Cette section est publiée depuis le CMS.',
            ]],
            media: [],
        );
    }

    /** @param list<string> $locales */
    private function storedProject(string $slug, string $status, array $locales): Project
    {
        $project = Project::factory()->create([
            'slug' => $slug,
            'publication_status' => $status,
            'published_at' => $status === 'published' ? now() : null,
            'is_featured' => true,
            'featured_order' => 10,
        ]);
        foreach ($locales as $locale) {
            $project->translations()->create([
                'locale' => $locale,
                'title' => $slug,
                'summary' => "Public {$locale} summary",
                'role' => 'Developer',
                'seo_title' => null,
                'seo_description' => null,
            ]);
        }

        return $project;
    }

    private function pngContents(): string
    {
        $content = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADElEQVQI12P4//8/AAX+Av7czFnnAAAAAElFTkSuQmCC',
            true,
        );
        self::assertIsString($content);

        return $content;
    }
}
