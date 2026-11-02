<?php

declare(strict_types=1);

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Projects\Models\Project;

final class ProjectsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'slug' => 'handicapacite',
                'summary' => 'Plateforme associative multilingue FR/EN/AR avec support RTL, back-office sécurisé, RBAC, CI/CD et déploiement sur trois environnements.',
                'technologies' => ['Laravel 13', 'PHP 8.3+', 'Sentry Laravel', 'Larastan', 'Pint', 'PHPUnit 12'],
                'featured' => true,
                'order' => 10,
            ],
            [
                'slug' => 'simulateur-ia',
                'summary' => 'Application Laravel 13 qui envoie des images à une API externe de traitement par IA. V1 livrée en 2 semaines, en production chez COSIKA.',
                'technologies' => ['Laravel 13', 'PHP 8.3+', 'PHPUnit', 'API REST'],
                'featured' => true,
                'order' => 15,
                'lifecycle' => 'maintained',
            ],
            [
                'slug' => 'revesta',
                'summary' => 'Dashboard Laravel avec gestion des utilisateurs, rôles, permissions, contenus et API.',
                'technologies' => ['Laravel 12', 'PHP 8.2+', 'Laravel Passport', 'PHPUnit', 'Scribe', 'Pint'],
                'featured' => true,
                'order' => 20,
            ],
            [
                'slug' => 'gm-exchange',
                'summary' => 'Application Laravel pour un bureau de change avec données de devises et métaux précieux, réservation et pages publiques.',
                'technologies' => ['Laravel 12', 'PHP 8.2+', 'PHPUnit', 'Pint'],
                'featured' => true,
                'order' => 30,
            ],
            [
                'slug' => 'portfolio',
                'summary' => 'Portfolio Laravel 13 modulaire, bilingue FR/EN, avec CMS privé Vue 3/Inertia, SEO et tests d’accessibilité.',
                'technologies' => ['Laravel 13', 'Blade', 'Vue 3', 'TypeScript', 'Inertia', 'PostgreSQL', 'Docker', 'Playwright'],
                'featured' => false,
                'order' => 40,
            ],
            [
                'slug' => 'educonnect',
                'summary' => 'Projet applicatif à analyser plus en détail.',
                'technologies' => [],
                'featured' => false,
                'order' => 50,
            ],
            [
                'slug' => 'taskmate',
                'summary' => 'Projet applicatif à analyser plus en détail.',
                'technologies' => [],
                'featured' => false,
                'order' => 60,
            ],
            [
                'slug' => 'lightyoulife',
                'summary' => 'Projet e-commerce Java en cours.',
                'technologies' => [
                    'Java', 'Spring Boot', 'Spring Security', 'Spring Data JPA/Hibernate',
                    'PostgreSQL', 'Thymeleaf', 'Stripe', 'Redis', 'RabbitMQ', 'Kafka',
                    'JUnit', 'Mockito', 'Testcontainers', 'Docker', 'Jenkins',
                ],
                'featured' => true,
                'order' => 70,
                'lifecycle' => 'in_progress',
            ],
        ];

        foreach ($projects as $row) {
            $project = Project::query()->firstOrCreate(['slug' => $row['slug']], [
                'uuid' => (string) Str::uuid(),
                'lifecycle_status' => $row['lifecycle'] ?? 'unspecified',
                'technologies' => $row['technologies'],
                'start_year' => null,
                'start_month' => null,
                'end_year' => null,
                'end_month' => null,
                'is_ongoing' => in_array($row['lifecycle'] ?? null, ['in_progress', 'maintained'], true),
                'repository_visibility' => 'none',
                'repository_url' => null,
                'show_repository' => false,
                'demo_url' => null,
                'show_demo' => false,
                'publication_status' => 'draft',
                'is_featured' => $row['featured'],
                'featured_order' => $row['featured'] ? $row['order'] : null,
                'sort_order' => $row['order'],
                'source_reference' => 'docs/content/projects.md',
                'published_at' => null,
            ]);
            $project->translations()->firstOrCreate(['locale' => 'fr'], [
                'title' => $this->title($row['slug']),
                'summary' => $row['summary'],
                'role' => '',
                'seo_title' => null,
                'seo_description' => null,
            ]);
        }

        $handicapacite = Project::query()->where('slug', 'handicapacite')->firstOrFail();
        $metrics = $handicapacite->caseStudySections()->firstOrCreate(['type' => 'metrics'], [
            'is_public' => false,
            'is_verified' => true,
            'sort_order' => 70,
        ]);
        $metrics->translations()->firstOrCreate(['locale' => 'fr'], [
            'heading' => 'Métriques vérifiées',
            'body' => '105 tests automatisés, 632 assertions et une accessibilité Lighthouse supérieure ou égale à 95/100.',
        ]);

        $this->sections($handicapacite, [
            ['context', 10, 'Contexte', 'Site d’une association libanaise d’inclusion, réalisé d’avril à juin 2026 dans le cadre du MBA, à titre bénévole. Le site est en production et utilisé par l’association.'],
            ['contribution', 50, 'Contribution', 'Développement complet : site Laravel 13 + Blade + MySQL découpé en modules, multilingue FR/AR (RTL)/EN, back-office (contenus, projets, actualités, rôles), formulaires, Docker en local, Pint et PHPStan.'],
            ['results', 60, 'Résultats', 'Déploiement sur OVH avec HTTPS, Sentry et sauvegardes. Plan de tests et procès-verbal de recette validés avec l’association.'],
        ]);

        $simulator = Project::query()->where('slug', 'simulateur-ia')->firstOrFail();
        $this->sections($simulator, [
            ['context', 10, 'Contexte', 'Projet interne COSIKA : un site Laravel 13 qui appelle une API externe de traitement d’images par un modèle d’IA. Le code est privé : seule la démarche est présentée.'],
            ['approach', 30, 'Approche', 'Architecture, développement piloté par les tests (TDD), planification et découpage des tâches, revue de chaque modification. Une partie du code a été écrite avec un assistant IA, sous ma direction.'],
            ['results', 60, 'Résultats', 'V1 livrée en 2 semaines, déployée en préproduction puis en production, conforme au cahier des charges. Une V2 est en cours.'],
        ]);

        $revesta = Project::query()->where('slug', 'revesta')->firstOrFail();
        $this->sections($revesta, [
            ['context', 10, 'Contexte', 'Projet de MBA mené par une équipe multidisciplinaire (« My Digital Start-Up »), mis en production.'],
            ['contribution', 50, 'Contribution', 'API backend, dashboard d’administration et coordination technique avec l’extension navigateur développée par l’équipe.'],
        ]);
    }

    /**
     * Verified but private sections: they become public only after review in the CMS.
     *
     * @param  list<array{0: string, 1: int, 2: string, 3: string}>  $sections
     */
    private function sections(Project $project, array $sections): void
    {
        foreach ($sections as [$type, $order, $heading, $body]) {
            $section = $project->caseStudySections()->firstOrCreate(['type' => $type], [
                'is_public' => false,
                'is_verified' => true,
                'sort_order' => $order,
            ]);
            $section->translations()->firstOrCreate(['locale' => 'fr'], [
                'heading' => $heading,
                'body' => $body,
            ]);
        }
    }

    private function title(string $slug): string
    {
        return match ($slug) {
            'handicapacite' => 'Handicapacité',
            'educonnect' => 'EduConnect',
            'gm-exchange' => 'GM Exchange',
            'simulateur-ia' => 'Simulateur IA',
            'lightyoulife' => 'LightYouLife',
            'taskmate' => 'TaskMate',
            default => Str::headline($slug),
        };
    }
}
