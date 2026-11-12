<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Career\Models\Achievement;
use Modules\Career\Models\Certification;
use Modules\Career\Models\EducationRecord;
use Modules\Career\Models\Experience;
use Modules\Career\Models\LanguageProficiency;
use Modules\Profile\Models\Profile;
use Modules\Projects\Models\Project;
use Modules\Skills\Models\Skill;
use Modules\Skills\Models\SkillCategory;

/**
 * Adds the profile (FR + EN) and the English translations of the French draft content created by the
 * other seeders. Idempotent (firstOrCreate), never overwrites what was edited in the dashboard, and
 * never publishes anything: publication stays a deliberate action in the CMS.
 *
 * Source of truth for the facts: docs/content/. Translations are a faithful rendering of the French
 * text, with no added claim or metric.
 */
final class EnglishContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->profile();
        $this->career();
        $this->skills();
        $this->projects();
    }

    private function profile(): void
    {
        $profile = Profile::query()->firstOrCreate(['key' => 'main'], [
            'display_name' => 'Alaa Khalil',
            'is_published' => false,
            'show_location' => false,
            'show_availability' => false,
            'published_at' => null,
        ]);

        $profile->translations()->firstOrCreate(['locale' => 'fr'], [
            'professional_titles' => ['Développeur Full-Stack PHP / Laravel', 'Chef de projet web technique / digital'],
            'summary' => 'Développeur Full-Stack spécialisé en PHP, Laravel et Vue.js, avec près de trois ans d’expérience en alternance sur des applications web métier. Le profil combine développement full-stack, API REST, architecture applicative, bases de données, optimisation des performances, sécurité, tests automatisés, CI/CD et pilotage de projets digitaux du cadrage au déploiement.',
            'location_label' => 'Paris et Île-de-France',
            'availability' => null,
            'biography' => 'Je développe des applications web métier en PHP / Laravel et Vue.js, de la conception de l’architecture et de la base de données jusqu’au déploiement. Je sais aussi piloter un projet technique : cadrage, planification, priorisation, coordination, recette et livraison.',
        ]);
        $profile->translations()->firstOrCreate(['locale' => 'en'], [
            'professional_titles' => ['Full-Stack PHP / Laravel Developer', 'Technical Web / Digital Project Manager'],
            'summary' => 'Full-Stack developer specialised in PHP, Laravel and Vue.js, with almost three years of work-study experience on business web applications. My profile combines full-stack development, REST APIs, application architecture, databases, performance optimisation, security, automated testing, CI/CD and digital project management from scoping to deployment.',
            'location_label' => 'Paris and the Île-de-France region',
            'availability' => null,
            'biography' => 'I build business web applications with PHP / Laravel and Vue.js, from architecture and database design through to deployment. I can also lead a technical project: scoping, planning, prioritisation, coordination, acceptance testing and delivery.',
        ]);
    }

    private function career(): void
    {
        $this->translate(Experience::query()->where('key', 'cosika')->first(), [
            'role' => 'PHP / Laravel Developer',
            'employment_type' => 'Work-study (2024-2026), then fixed-term contract',
            'location' => 'Les Herbiers, France',
            'summary' => null,
            'highlights' => [
                'Migration of the Simply business application from Laravel 6 to Laravel 11, and release to production',
                'AI simulator (Laravel 13 + external image-processing API): V1 delivered in 2 weeks to staging and production; architecture, TDD, task breakdown and code review',
                'Parallel work on 3 Laravel applications sharing the same database',
                'Audit, estimate and breakdown plan for moving the Digital Catalogue to a modular architecture (subject of the MBA thesis)',
                'Evolution of an ERP, a digital catalogue and REST APIs for mobile applications',
                'Full-stack development with Laravel / Vue.js',
                'API endpoint security, RBAC and middleware',
                'SQL optimisation and performance',
                'PHPUnit and Playwright E2E tests',
                'Code reviews, GitLab CI/CD and Sentry',
                'Analysis of changes, feasibility, breakdown, prioritisation and quality follow-up',
            ],
        ]);
        $this->translate(Experience::query()->where('key', 'open-campus-angers')->first(), [
            'role' => 'IT Assistant & Web Developer',
            'employment_type' => 'Work-study',
            'location' => 'Angers, France',
            'summary' => null,
            'highlights' => [
                'Requirements analysis and a Laravel application for academic management',
                'Timetables, performance, projects and grading',
                'MySQL design, authentication and access management',
                'Linux / Nginx deployment and IT support',
                'Python automation to generate student cards',
                'User support',
            ],
        ]);

        $achievement = Achievement::query()->where('key', 'catalogue-sql-performance')->first();
        $achievement?->translations()->firstOrCreate(['locale' => 'en'], [
            'statement' => 'A catalogue optimisation reduced one measured case from more than 385 SQL queries to 6, and backend time from about 1.7 seconds to 34 ms.',
        ]);

        $education = [
            'manager-projet-web-digital' => ['program' => 'Web and Digital Project Manager', 'level' => 'RNCP level 7 qualification (master’s level)', 'location' => 'Angers, France'],
            'bachelor-concepteur-developpeur-applications' => ['program' => 'Bachelor in Application Design and Development', 'level' => null, 'location' => 'Angers, France'],
        ];
        foreach ($education as $key => $fields) {
            $this->translate(EducationRecord::query()->where('key', $key)->first(), [...$fields, 'summary' => null]);
        }

        Certification::query()->where('key', 'toeic-2026')->first()?->translations()->firstOrCreate(['locale' => 'en'], [
            'name' => 'TOEIC',
            'skill_label' => 'Professional English',
        ]);

        $languages = [
            'francais' => ['name' => 'French', 'proficiency_label' => 'Bilingual', 'evidence' => null],
            'anglais' => ['name' => 'English', 'proficiency_label' => 'B2', 'evidence' => 'TOEIC 940 in May 2026'],
            'arabe' => ['name' => 'Arabic', 'proficiency_label' => 'Native language', 'evidence' => null],
        ];
        foreach ($languages as $key => $fields) {
            $this->translate(LanguageProficiency::query()->where('key', $key)->first(), $fields);
        }
    }

    private function skills(): void
    {
        $categories = [
            'backend' => 'Backend',
            'donnees' => 'Data',
            'tests-et-qualite' => 'Testing and quality',
            'frontend' => 'Frontend',
            'devops-et-infrastructure' => 'DevOps and infrastructure',
            'architecture-et-outils' => 'Architecture and tools',
            'pilotage' => 'Project management',
        ];
        foreach ($categories as $key => $name) {
            SkillCategory::query()->where('key', $key)->first()
                ?->translations()->firstOrCreate(['locale' => 'en'], ['name' => $name]);
        }

        // Only the names that differ in English; technology names are kept as they are.
        $names = [
            'POO' => 'OOP',
            'indexation' => 'Indexing',
            'optimisation SQL' => 'SQL optimization',
            'Cadrage' => 'Scoping',
            'planification' => 'Planning',
            'priorisation' => 'Prioritization',
            'suivi d’avancement' => 'Progress tracking',
            'analyse des besoins' => 'Requirements analysis',
            'faisabilité' => 'Feasibility analysis',
            'spécifications fonctionnelles et techniques' => 'Functional and technical specifications',
            'sprints' => 'Sprints',
            'jalons' => 'Milestones',
            'recette' => 'Acceptance testing',
            'amélioration continue' => 'Continuous improvement',
            'coordination des parties prenantes' => 'Stakeholder coordination',
        ];

        foreach (Skill::query()->with('translations')->get() as $skill) {
            $french = $skill->translations->firstWhere('locale', 'fr');
            if ($french === null) {
                continue;
            }
            $english = $names[$french->name] ?? $french->name;
            $skill->translations()->firstOrCreate(['locale' => 'en'], ['name' => $english, 'description' => null]);
        }
    }

    private function projects(): void
    {
        $summaries = [
            'handicapacite' => 'Multilingual (FR/EN/AR) non-profit platform with RTL support, a secured back office, RBAC, CI/CD and deployment across three environments.',
            'simulateur-ia' => 'Laravel 13 application that sends images to an external AI processing API. V1 delivered in 2 weeks and in production at COSIKA.',
            'revesta' => 'Laravel dashboard with user, role, permission and content management, plus an API.',
            'gm-exchange' => 'Laravel application for a currency exchange office, with currency and precious-metal data, booking and public pages.',
            'portfolio' => 'Modular Laravel 13 portfolio, bilingual FR/EN, with a private Vue 3/Inertia CMS, SEO and accessibility tests.',
            'educonnect' => 'Laravel 10 application with a home page, authentication and a dashboard. An early training project, to be completed before publication.',
            'taskmate' => 'Project and task manager built with Laravel 11, with lists, comments and attachments, using the AdminLTE interface.',
            'lightyoulife' => 'Java e-commerce project, in progress.',
            'strive' => 'Mobile app for tracking sports activities: GPS routes (start, pause, resume, stop), map, statistics, GPX/CSV export, local SQLite data. School project.',
            'trip-flow' => 'Mobile app for trip planning: trips, geolocated stops on a map, checklist and photo journal, local SQLite data.',
            'trixscore' => 'Progressive web app for scoring the card game Trex: individual and team modes, history, editable scoring rules, works offline. Plain JavaScript, no build step.',
            'pwa-camera' => 'Progressive web app: photo capture, geolocation on a map and real-time messaging through an Express + Socket.IO server.',
            'todo-app' => 'Containerised full-stack task manager: React front end, Express REST API, MySQL, started with a single docker compose up.',
            'github-actions' => 'Reusable template: an issue form and 4 GitHub Actions workflows that title, label and assign the issue and create its release branch.',
            'todo-list' => 'Small React to-do list with colour-coded tags, stored in the browser, tested with Jest and Testing Library.',
        ];
        $titles = [
            'handicapacite' => 'Handicapacité',
            'educonnect' => 'EduConnect',
            'gm-exchange' => 'GM Exchange',
            'simulateur-ia' => 'AI Simulator',
            'lightyoulife' => 'LightYouLife',
            'taskmate' => 'TaskMate',
            'trixscore' => 'TrixScore',
            'trip-flow' => 'Trip Flow',
            'pwa-camera' => 'PWA Camera',
            'todo-app' => 'Todo App',
            'todo-list' => 'Todo List',
            'github-actions' => 'GitHub Actions workflows',
        ];

        foreach ($summaries as $slug => $summary) {
            $project = Project::query()->where('slug', $slug)->first();
            $project?->translations()->firstOrCreate(['locale' => 'en'], [
                'title' => $titles[$slug] ?? Str::headline($slug),
                'summary' => $summary,
                'role' => '',
                'seo_title' => null,
                'seo_description' => null,
            ]);
        }

        $sections = [
            'handicapacite' => [
                'metrics' => ['Verified metrics', '105 automated tests, 632 assertions and a Lighthouse accessibility score of 95/100 or higher.'],
                'context' => ['Context', 'Website of a Lebanese inclusion association, built from April to June 2026 as part of the MBA, on a voluntary basis. The site is in production and used by the association.'],
                'contribution' => ['Contribution', 'Full development: a Laravel 13 + Blade + MySQL site split into modules, multilingual FR/AR (RTL)/EN, a back office (content, projects, news, roles), forms, local Docker, Pint and PHPStan.'],
                'results' => ['Results', 'Deployment on OVH with HTTPS, Sentry and backups. Test plan and acceptance report validated with the association.'],
            ],
            'simulateur-ia' => [
                'context' => ['Context', 'Internal COSIKA project: a Laravel 13 site that calls an external API that processes images with an AI model. The code is private, so only the approach is presented.'],
                'approach' => ['Approach', 'Architecture, test-driven development (TDD), planning and task breakdown, and review of every change. Part of the code was written with an AI assistant, under my direction.'],
                'results' => ['Results', 'V1 delivered in 2 weeks, deployed to staging then production, in line with the specification. A V2 is in progress.'],
            ],
            'revesta' => [
                'context' => ['Context', 'MBA project run by a multidisciplinary team (“My Digital Start-Up”), put into production.'],
                'contribution' => ['Contribution', 'Backend API, admin dashboard and technical coordination with the browser extension built by the team.'],
            ],
        ];
        foreach ($sections as $slug => $bySection) {
            $project = Project::query()->where('slug', $slug)->first();
            if ($project === null) {
                continue;
            }
            foreach ($bySection as $type => [$heading, $body]) {
                $section = $project->caseStudySections()->where('type', $type)->first();
                $section?->translations()->firstOrCreate(['locale' => 'en'], ['heading' => $heading, 'body' => $body]);
            }
        }
    }

    /**
     * @param  Experience|EducationRecord|LanguageProficiency|null  $record
     * @param  array<string, mixed>  $fields
     */
    private function translate(mixed $record, array $fields): void
    {
        $record?->translations()->firstOrCreate(['locale' => 'en'], $fields);
    }
}
