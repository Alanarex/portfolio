<?php

declare(strict_types=1);

namespace Modules\Career\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Career\Models\Achievement;
use Modules\Career\Models\Certification;
use Modules\Career\Models\EducationRecord;
use Modules\Career\Models\Experience;
use Modules\Career\Models\LanguageProficiency;

final class CareerDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $cosika = Experience::query()->firstOrCreate(['key' => 'cosika'], [
            'organization' => 'COSIKA',
            'start_year' => 2024,
            'start_month' => 9,
            'end_year' => null,
            'end_month' => null,
            'is_current' => true,
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 10,
        ]);
        $cosika->translations()->firstOrCreate(['locale' => 'fr'], [
            'role' => 'Développeur PHP / Laravel',
            'employment_type' => 'Alternance (2024-2026) puis CDD',
            'location' => 'Les Herbiers',
            'summary' => null,
            'highlights' => [
                'Migration de l’application métier Simply de Laravel 6 à Laravel 11 et mise en production',
                'Simulateur IA (Laravel 13 + API externe de traitement d’images) : V1 livrée en 2 semaines en préproduction et production ; architecture, TDD, découpage des tâches et revue du code',
                'Travail en parallèle sur 3 applications Laravel partageant la même base de données',
                'Audit, chiffrage et plan de découpage du Catalogue Digital vers une architecture modulaire (sujet du mémoire de MBA)',
                'Évolution d’un ERP, d’un catalogue digital et d’API REST pour applications mobiles',
                'Développement full-stack Laravel / Vue.js',
                'Sécurisation des endpoints API, RBAC et middlewares',
                'Optimisation SQL et performance',
                'Tests PHPUnit et Playwright E2E',
                'Revues de code, GitLab CI/CD et Sentry',
                'Analyse des évolutions, faisabilité, découpage, priorisation et suivi qualité',
            ],
        ]);
        $achievement = Achievement::query()->firstOrCreate([
            'experience_id' => $cosika->getKey(),
            'key' => 'catalogue-sql-performance',
        ], [
            'status' => 'published',
            'is_quantified' => true,
            'is_verified' => true,
            'source_reference' => 'docs/content/experience.md',
            'sort_order' => 10,
        ]);
        $achievement->translations()->firstOrCreate(['locale' => 'fr'], [
            'statement' => 'Une optimisation du catalogue a réduit un cas mesuré de plus de 385 requêtes SQL à 6 et le temps backend d’environ 1,7 seconde à 34 ms.',
        ]);

        foreach ([
            ['simply-laravel-migration', 20, 'Migration de l’application métier Simply de Laravel 6 à Laravel 11, mise en production.'],
            ['simulateur-ia-v1', 30, 'Première version d’un simulateur IA livrée en 2 semaines, en préproduction puis en production.'],
        ] as [$key, $order, $statement]) {
            $result = Achievement::query()->firstOrCreate(['experience_id' => $cosika->getKey(), 'key' => $key], [
                'status' => 'published',
                'is_quantified' => true,
                'is_verified' => true,
                'source_reference' => 'docs/content/experience.md',
                'sort_order' => $order,
            ]);
            $result->translations()->firstOrCreate(['locale' => 'fr'], ['statement' => $statement]);
        }

        foreach ([
            ['key' => 'revesta', 'organization' => 'REVESTA', 'year' => 2024, 'month' => 12, 'order' => 20, 'summary' => 'REVESTA : l’extension qui identifie vos aides à la rénovation.'],
            ['key' => 'cojeco', 'organization' => 'CoJeCo', 'year' => 2025, 'month' => 6, 'order' => 30, 'summary' => 'CoJeCo : Collectif des jeunes connectés.'],
        ] as $row) {
            $founder = Experience::query()->firstOrCreate(['key' => $row['key']], [
                'organization' => $row['organization'],
                'start_year' => $row['year'],
                'start_month' => $row['month'],
                'end_year' => null,
                'end_month' => null,
                'is_current' => true,
                'status' => 'published',
                'published_at' => now(),
                'sort_order' => $row['order'],
            ]);
            $founder->translations()->firstOrCreate(['locale' => 'fr'], [
                'role' => 'Co-fondateur',
                'employment_type' => null,
                'location' => 'Angers (hybride)',
                'summary' => $row['summary'],
                'highlights' => [],
            ]);
        }

        $openCampus = Experience::query()->firstOrCreate(['key' => 'open-campus-angers'], [
            'organization' => 'Open Campus Angers',
            'start_year' => 2023,
            'start_month' => 11,
            'end_year' => 2024,
            'end_month' => 8,
            'is_current' => false,
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 40,
        ]);
        $openCampus->translations()->firstOrCreate(['locale' => 'fr'], [
            'role' => 'Assistant informatique & Développeur web',
            'employment_type' => 'Alternance',
            'location' => 'Angers',
            'summary' => null,
            'highlights' => [
                'Analyse des besoins et application Laravel de gestion pédagogique',
                'Plannings, performances, projets et notation',
                'Conception MySQL, authentification et gestion des accès',
                'Déploiement Linux / Nginx et support informatique',
                'Automatisation Python pour la génération de cartes étudiantes',
                'Accompagnement utilisateur',
            ],
        ]);

        $education = [
            ['key' => 'manager-projet-web-digital', 'institution' => 'MyDigitalSchool', 'start_year' => 2024, 'end_year' => 2026, 'sort_order' => 10, 'program' => 'Manager de Projet Web et Digital', 'level' => 'Titre RNCP niveau 7', 'location' => 'Angers'],
            ['key' => 'bachelor-concepteur-developpeur-applications', 'institution' => 'Open Campus', 'start_year' => 2023, 'end_year' => 2024, 'sort_order' => 20, 'program' => 'Bachelor Concepteur Développeur d’Applications', 'level' => null, 'location' => 'Angers'],
            ['key' => 'universite-angers-bac-2', 'institution' => 'Université d’Angers', 'start_year' => 2023, 'end_year' => 2023, 'sort_order' => 30, 'program' => 'Programmation informatique, formation générale', 'level' => 'Bac+2', 'location' => null],
        ];
        foreach ($education as $row) {
            $record = EducationRecord::query()->firstOrCreate(['key' => $row['key']], [
                'institution' => $row['institution'],
                'start_year' => $row['start_year'],
                'start_month' => null,
                'end_year' => $row['end_year'],
                'end_month' => $row['key'] === 'universite-angers-bac-2' ? 6 : null,
                'status' => 'published',
                'published_at' => now(),
                'sort_order' => $row['sort_order'],
            ]);
            $record->translations()->firstOrCreate(['locale' => 'fr'], [
                'program' => $row['program'],
                'level' => $row['level'],
                'location' => $row['location'],
                'summary' => null,
            ]);
        }

        $toeic = Certification::query()->firstOrCreate(['key' => 'toeic-2026'], [
            'issuer' => null,
            'credential_id' => null,
            'verification_url' => null,
            'issue_year' => 2026,
            'issue_month' => 5,
            'result' => '940',
            'is_verified' => true,
            'source_reference' => 'docs/content/certifications.md',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 10,
        ]);
        $toeic->translations()->firstOrCreate(['locale' => 'fr'], [
            'name' => 'TOEIC',
            'skill_label' => 'Anglais professionnel',
        ]);

        $unity = Certification::query()->firstOrCreate(['key' => 'unity-junior-programmer'], [
            'issuer' => 'Unity',
            'credential_id' => null,
            'verification_url' => 'https://www.credly.com/badges/3212a7dd-3e83-42bd-ac55-9c7e3274e178/linked_in_profile',
            'issue_year' => 2023,
            'issue_month' => 6,
            'result' => null,
            'is_verified' => true,
            'source_reference' => 'docs/content/certifications.md',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 20,
        ]);
        if ($unity->verification_url === null) {
            $unity->forceFill(['verification_url' => 'https://www.credly.com/badges/3212a7dd-3e83-42bd-ac55-9c7e3274e178/linked_in_profile'])->save();
        }
        $unity->translations()->firstOrCreate(['locale' => 'fr'], [
            'name' => 'Unity Junior Programmer',
            'skill_label' => 'Développement de jeux avec Unity et C#',
        ]);

        $languages = [
            ['key' => 'francais', 'code' => 'fr', 'kind' => 'bilingual', 'cefr' => null, 'order' => 10, 'name' => 'Français', 'label' => 'Bilingue', 'evidence' => null],
            ['key' => 'anglais', 'code' => 'en', 'kind' => 'cefr', 'cefr' => 'B2', 'order' => 20, 'name' => 'Anglais', 'label' => 'B2', 'evidence' => 'TOEIC 940 en mai 2026'],
            ['key' => 'arabe', 'code' => 'ar', 'kind' => 'native', 'cefr' => null, 'order' => 30, 'name' => 'Arabe', 'label' => 'Langue maternelle', 'evidence' => null],
        ];
        foreach ($languages as $row) {
            $language = LanguageProficiency::query()->firstOrCreate(['key' => $row['key']], [
                'language_code' => $row['code'],
                'proficiency_kind' => $row['kind'],
                'cefr_level' => $row['cefr'],
                'status' => 'published',
                'published_at' => now(),
                'sort_order' => $row['order'],
            ]);
            $language->translations()->firstOrCreate(['locale' => 'fr'], [
                'name' => $row['name'],
                'proficiency_label' => $row['label'],
                'evidence' => $row['evidence'],
            ]);
        }
    }
}
