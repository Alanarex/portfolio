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
            // Public GitHub repositories only (READMEs checked on 08/11/2026). Client, company, abandoned and
            // school-internal projects are deliberately never seeded; TaskMate, Todo App and Todo List are
            // held back until reworked. Drafts until reviewed in the CMS.
            [
                'slug' => 'strive',
                'summary' => 'Application mobile de suivi d’activités sportives : parcours GPS (démarrer, pause, reprise, arrêt), carte, statistiques, export GPX/CSV, données locales en SQLite. Projet scolaire.',
                'technologies' => ['React Native', 'Expo', 'TypeScript', 'SQLite'],
                'featured' => true,
                'order' => 10,
                'lifecycle' => 'completed',
                'repo' => 'https://github.com/Alanarex/strive',
                'skills' => ['typescript', 'javascript'],
                'demo' => null,
            ],
            [
                'slug' => 'trip-flow',
                'summary' => 'Application mobile de préparation de voyages : voyages, étapes géolocalisées sur carte, checklist et journal avec photos, données locales en SQLite.',
                'technologies' => ['React Native', 'Expo', 'TypeScript', 'SQLite'],
                'featured' => true,
                'order' => 20,
                'lifecycle' => 'completed',
                'repo' => 'https://github.com/Alanarex/trip-flow-1',
                'skills' => ['typescript', 'javascript'],
                'demo' => null,
            ],
            [
                'slug' => 'trixscore',
                'summary' => 'Application web progressive pour compter les points au Trex : modes individuel et équipes, historique, barèmes modifiables, hors ligne. JavaScript pur, sans build.',
                'technologies' => ['JavaScript', 'HTML', 'CSS', 'PWA'],
                'featured' => true,
                'order' => 30,
                'lifecycle' => 'completed',
                'repo' => 'https://github.com/Alanarex/TrixScore',
                'demo' => 'https://alanarex.github.io/TrixScore/',
                'skills' => ['javascript'],
            ],
            [
                'slug' => 'pwa-camera',
                'summary' => 'Application web progressive : prise de photos, géolocalisation sur carte et messagerie temps réel via un serveur Express + Socket.IO.',
                'technologies' => ['React', 'TypeScript', 'Vite', 'Express', 'Socket.IO', 'Leaflet'],
                'featured' => false,
                'order' => 110,
                'lifecycle' => 'completed',
                'repo' => 'https://github.com/Alanarex/typescript_camera',
                'skills' => ['typescript', 'javascript', 'vite'],
                'demo' => null,
            ],
            [
                'slug' => 'github-actions',
                'summary' => 'Modèle réutilisable : un formulaire d’issue et 4 workflows GitHub Actions qui titrent, étiquettent, assignent l’issue et créent sa branche de release.',
                'technologies' => ['GitHub Actions', 'YAML'],
                'featured' => false,
                'order' => 130,
                'lifecycle' => 'completed',
                'repo' => 'https://github.com/Alanarex/github-actions',
                'skills' => ['github-actions', 'git'],
                'demo' => null,
            ],
        ];

        foreach ($projects as $row) {
            $project = Project::query()->firstOrCreate(['slug' => $row['slug']], [
                'uuid' => (string) Str::uuid(),
                'lifecycle_status' => $row['lifecycle'],
                'technologies' => $row['technologies'],
                'start_year' => null,
                'start_month' => null,
                'end_year' => null,
                'end_month' => null,
                'is_ongoing' => false,
                'repository_visibility' => 'public',
                'repository_url' => $row['repo'],
                'show_repository' => true,
                'demo_url' => $row['demo'],
                'show_demo' => $row['demo'] !== null,
                'publication_status' => 'draft',
                'is_featured' => $row['featured'],
                'featured_order' => $row['featured'] ? $row['order'] : null,
                'sort_order' => $row['order'],
                'source_reference' => 'docs/content/projects.md',
                'published_at' => null,
            ]);
            foreach ($row['skills'] as $skillKey) {
                $project->skills()->firstOrCreate(['skill_key' => $skillKey]);
            }
            $project->translations()->firstOrCreate(['locale' => 'fr'], [
                'title' => $this->title($row['slug']),
                'summary' => $row['summary'],
                'role' => '',
                'seo_title' => null,
                'seo_description' => null,
            ]);
        }
    }

    private function title(string $slug): string
    {
        return match ($slug) {
            'trixscore' => 'TrixScore',
            'trip-flow' => 'Trip Flow',
            'pwa-camera' => 'PWA Camera',
            'github-actions' => 'GitHub Actions workflows',
            default => Str::headline($slug),
        };
    }
}
