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
            'status' => 'draft',
            'sort_order' => 10,
        ]);
        $cosika->translations()->firstOrCreate(['locale' => 'fr'], [
            'role' => 'Développeur PHP / Laravel',
            'employment_type' => 'Alternance',
            'location' => 'Les Herbiers',
            'summary' => null,
            'highlights' => [
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
            'status' => 'draft',
            'is_quantified' => true,
            'is_verified' => true,
            'source_reference' => 'docs/content/experience.md',
            'sort_order' => 10,
        ]);
        $achievement->translations()->firstOrCreate(['locale' => 'fr'], [
            'statement' => 'Une optimisation du catalogue a réduit un cas mesuré de plus de 385 requêtes SQL à 6 et le temps backend d’environ 1,7 seconde à 34 ms.',
        ]);

        $openCampus = Experience::query()->firstOrCreate(['key' => 'open-campus-angers'], [
            'organization' => 'Open Campus Angers',
            'start_year' => 2023,
            'start_month' => 11,
            'end_year' => 2024,
            'end_month' => 8,
            'is_current' => false,
            'status' => 'draft',
            'sort_order' => 20,
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
        ];
        foreach ($education as $row) {
            $record = EducationRecord::query()->firstOrCreate(['key' => $row['key']], [
                'institution' => $row['institution'],
                'start_year' => $row['start_year'],
                'start_month' => null,
                'end_year' => $row['end_year'],
                'end_month' => null,
                'status' => 'draft',
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
            'status' => 'draft',
            'sort_order' => 10,
        ]);
        $toeic->translations()->firstOrCreate(['locale' => 'fr'], [
            'name' => 'TOEIC',
            'skill_label' => 'Anglais professionnel',
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
                'status' => 'draft',
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
