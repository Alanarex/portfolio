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
                'summary' => 'Portfolio interactif avec animations et expérience 3D.',
                'technologies' => [],
                'featured' => true,
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
                'is_ongoing' => ($row['lifecycle'] ?? null) === 'in_progress',
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
    }

    private function title(string $slug): string
    {
        return match ($slug) {
            'handicapacite' => 'Handicapacité',
            'educonnect' => 'EduConnect',
            'gm-exchange' => 'GM Exchange',
            'lightyoulife' => 'LightYouLife',
            'taskmate' => 'TaskMate',
            default => Str::headline($slug),
        };
    }
}
