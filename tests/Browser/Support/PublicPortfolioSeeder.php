<?php

declare(strict_types=1);

namespace Tests\Browser\Support;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use LogicException;
use Modules\Career\Models\Achievement;
use Modules\Career\Models\Certification;
use Modules\Career\Models\EducationRecord;
use Modules\Career\Models\Experience;
use Modules\Profile\Models\Profile;
use Modules\Projects\Models\Project;
use Modules\Settings\Models\SiteSetting;
use Modules\Skills\Models\SkillCategory;

final class PublicPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('testing')) {
            throw new LogicException('Browser fixtures may only be seeded in the testing environment.');
        }

        $this->profile();
        $this->settings();
        $this->career();
        $this->skills();
        $this->projects();
    }

    private function profile(): void
    {
        $profile = Profile::query()->create([
            'key' => 'main',
            'display_name' => 'Alaa Khalil',
            'is_published' => true,
            'show_location' => true,
            'show_availability' => true,
            'published_at' => now(),
        ]);
        $profile->translations()->createMany([
            [
                'locale' => 'fr',
                'professional_titles' => ['Développeur PHP / Laravel', 'Full-stack', 'Pilotage technique'],
                'summary' => 'Je conçois des applications métier robustes, accessibles et prêtes pour la production.',
                'location_label' => 'France',
                'availability' => 'Disponible pour échanger',
                'biography' => 'Mon parcours relie développement full-stack, architecture modulaire, qualité logicielle et compréhension produit.',
            ],
            [
                'locale' => 'en',
                'professional_titles' => ['PHP / Laravel Developer', 'Full-stack', 'Technical delivery'],
                'summary' => 'I build robust, accessible, production-ready business applications.',
                'location_label' => 'France',
                'availability' => 'Available to connect',
                'biography' => 'My work connects full-stack development, modular architecture, software quality, and product understanding.',
            ],
        ]);
    }

    private function settings(): void
    {
        $settings = SiteSetting::query()->create([
            'key' => 'main',
            'site_name' => 'Alaa Khalil',
            'default_locale' => 'fr',
            'contact_email' => 'contact@example.test',
            'contact_phone' => null,
            'show_email' => false,
            'show_phone' => false,
        ]);
        $settings->socialLinks()->create([
            'platform' => 'github',
            'url' => 'https://github.com/Alanarex',
            'is_enabled' => true,
            'is_public' => true,
            'sort_order' => 10,
        ]);
        $settings->featureFlags()->createMany([
            ['key' => 'projects', 'enabled' => true],
            ['key' => 'contact', 'enabled' => true],
            ['key' => 'cv', 'enabled' => false],
            ['key' => 'activity', 'enabled' => true],
            ['key' => 'three_d', 'enabled' => false],
        ]);
    }

    private function career(): void
    {
        $experience = Experience::query()->create([
            'key' => 'cosika',
            'organization' => 'COSIKA',
            'start_year' => 2024,
            'start_month' => 9,
            'end_year' => null,
            'end_month' => null,
            'is_current' => true,
            'status' => 'published',
            'sort_order' => 10,
            'published_at' => now(),
        ]);
        $experience->translations()->createMany([
            [
                'locale' => 'fr',
                'role' => 'Développeur PHP / Laravel',
                'employment_type' => 'Alternance',
                'location' => 'Les Herbiers',
                'summary' => 'Évolution d’applications métier, d’API et de parcours full-stack.',
                'highlights' => ['Laravel et Vue.js', 'Optimisation SQL', 'Tests et CI/CD'],
            ],
            [
                'locale' => 'en',
                'role' => 'PHP / Laravel Developer',
                'employment_type' => 'Apprenticeship',
                'location' => 'Les Herbiers',
                'summary' => 'Business application, API, and full-stack product development.',
                'highlights' => ['Laravel and Vue.js', 'SQL optimization', 'Testing and CI/CD'],
            ],
        ]);
        $achievement = Achievement::query()->create([
            'experience_id' => $experience->id,
            'key' => 'catalogue-sql-performance',
            'status' => 'published',
            'is_quantified' => true,
            'is_verified' => true,
            'source_reference' => 'docs/content/experience.md',
            'sort_order' => 10,
        ]);
        $achievement->translations()->createMany([
            [
                'locale' => 'fr',
                'statement' => 'Un cas mesuré du catalogue est passé de plus de 385 requêtes SQL à 6 et d’environ 1,7 seconde à 34 ms.',
            ],
            [
                'locale' => 'en',
                'statement' => 'A measured catalogue case went from more than 385 SQL queries to 6 and from about 1.7 seconds to 34 ms.',
            ],
        ]);

        $education = EducationRecord::query()->create([
            'key' => 'manager-projet-web-digital',
            'institution' => 'MyDigitalSchool',
            'start_year' => 2024,
            'start_month' => 9,
            'end_year' => 2026,
            'end_month' => 9,
            'status' => 'published',
            'sort_order' => 10,
            'published_at' => now(),
        ]);
        $education->translations()->createMany([
            ['locale' => 'fr', 'program' => 'Manager de Projet Web et Digital', 'level' => 'RNCP niveau 7', 'location' => 'Angers', 'summary' => null],
            ['locale' => 'en', 'program' => 'Web and Digital Project Manager', 'level' => 'EQF level 7', 'location' => 'Angers', 'summary' => null],
        ]);

        $certification = Certification::query()->create([
            'key' => 'toeic-2026',
            'issuer' => 'ETS Global',
            'credential_id' => null,
            'verification_url' => null,
            'issue_year' => 2026,
            'issue_month' => 5,
            'result' => '940',
            'is_verified' => true,
            'source_reference' => 'docs/content/certifications.md',
            'status' => 'published',
            'sort_order' => 10,
            'published_at' => now(),
        ]);
        $certification->translations()->createMany([
            ['locale' => 'fr', 'name' => 'TOEIC — 940', 'skill_label' => 'Anglais professionnel'],
            ['locale' => 'en', 'name' => 'TOEIC — 940', 'skill_label' => 'Professional English'],
        ]);
    }

    private function skills(): void
    {
        $categories = [
            'backend' => [
                'fr' => 'Backend',
                'en' => 'Backend',
                'skills' => ['PHP', 'Laravel', 'API REST', 'Architecture modulaire'],
            ],
            'frontend' => [
                'fr' => 'Frontend',
                'en' => 'Frontend',
                'skills' => ['Vue.js', 'React', 'TypeScript', 'Blade'],
            ],
            'quality' => [
                'fr' => 'Qualité et DevOps',
                'en' => 'Quality and DevOps',
                'skills' => ['PHPUnit', 'Playwright', 'Docker', 'GitHub Actions'],
            ],
        ];

        $categoryOrder = 10;
        foreach ($categories as $categoryKey => $data) {
            $category = SkillCategory::query()->create([
                'key' => $categoryKey,
                'status' => 'published',
                'is_visible' => true,
                'sort_order' => $categoryOrder,
                'published_at' => now(),
            ]);
            $category->translations()->createMany([
                ['locale' => 'fr', 'name' => $data['fr']],
                ['locale' => 'en', 'name' => $data['en']],
            ]);
            foreach ($data['skills'] as $index => $name) {
                $skill = $category->skills()->create([
                    'key' => Str::slug($name),
                    'status' => 'published',
                    'is_visible' => true,
                    'sort_order' => ($index + 1) * 10,
                    'published_at' => now(),
                ]);
                $skill->translations()->createMany([
                    ['locale' => 'fr', 'name' => $name, 'description' => null],
                    ['locale' => 'en', 'name' => $name, 'description' => null],
                ]);
            }
            $categoryOrder += 10;
        }
    }

    private function projects(): void
    {
        $projects = [
            [
                'slug' => 'handicapacite',
                'status' => 'maintained',
                'technologies' => ['Laravel 13', 'PHP 8.3', 'PostgreSQL', 'Playwright'],
                'fr' => ['Handicapacité', 'Plateforme associative multilingue FR/EN/AR, accessible et déployée sur trois environnements.'],
                'en' => ['Handicapacité', 'Accessible FR/EN/AR nonprofit platform deployed across three environments.'],
            ],
            [
                'slug' => 'gm-exchange',
                'status' => 'completed',
                'technologies' => ['Laravel 12', 'PHP', 'MySQL', 'Vite'],
                'fr' => ['GM Exchange', 'Application pour un bureau de change avec devises, métaux précieux et réservation.'],
                'en' => ['GM Exchange', 'Currency exchange application with precious metals data and reservations.'],
            ],
            [
                'slug' => 'portfolio',
                'status' => 'in_progress',
                'technologies' => ['Laravel 13', 'Blade', 'Vue.js', 'Docker'],
                'fr' => ['Portfolio V3', 'Portfolio social, bilingue et piloté par un CMS privé.'],
                'en' => ['Portfolio V3', 'Bilingual social portfolio managed through a private CMS.'],
            ],
        ];

        foreach ($projects as $index => $data) {
            $project = Project::query()->create([
                'uuid' => (string) Str::uuid(),
                'slug' => $data['slug'],
                'lifecycle_status' => $data['status'],
                'technologies' => $data['technologies'],
                'start_year' => 2025,
                'start_month' => 1,
                'end_year' => $data['status'] === 'in_progress' ? null : 2026,
                'end_month' => $data['status'] === 'in_progress' ? null : 6,
                'is_ongoing' => $data['status'] === 'in_progress',
                'repository_visibility' => 'none',
                'repository_url' => null,
                'show_repository' => false,
                'demo_url' => null,
                'show_demo' => false,
                'publication_status' => 'published',
                'is_featured' => true,
                'featured_order' => ($index + 1) * 10,
                'sort_order' => ($index + 1) * 10,
                'source_reference' => 'tests/browser',
                'published_at' => now(),
            ]);
            $project->translations()->createMany([
                ['locale' => 'fr', 'title' => $data['fr'][0], 'summary' => $data['fr'][1], 'role' => 'Développeur full-stack', 'seo_title' => null, 'seo_description' => null],
                ['locale' => 'en', 'title' => $data['en'][0], 'summary' => $data['en'][1], 'role' => 'Full-stack developer', 'seo_title' => null, 'seo_description' => null],
            ]);
            $section = $project->caseStudySections()->create([
                'type' => 'context',
                'is_public' => true,
                'is_verified' => true,
                'sort_order' => 10,
            ]);
            $section->translations()->createMany([
                ['locale' => 'fr', 'heading' => 'Contexte', 'body' => $data['fr'][1]],
                ['locale' => 'en', 'heading' => 'Context', 'body' => $data['en'][1]],
            ]);
        }
    }
}
