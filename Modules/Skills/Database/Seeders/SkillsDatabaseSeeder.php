<?php

declare(strict_types=1);

namespace Modules\Skills\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Skills\Models\Skill;
use Modules\Skills\Models\SkillCategory;

final class SkillsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $taxonomy = [
            'Backend' => ['PHP', 'Laravel', 'Java', 'Spring Boot', 'API REST', 'MVC', 'POO', 'RBAC'],
            'Données' => ['MySQL', 'MariaDB', 'PostgreSQL', 'SQL', 'JPA/Hibernate', 'migrations', 'indexation', 'optimisation SQL'],
            'Tests et qualité' => ['PHPUnit', 'Playwright', 'JUnit', 'Mockito', 'PHPStan/Larastan', 'Postman', 'Laravel Pint'],
            'Frontend' => ['Vue.js', 'JavaScript', 'TypeScript', 'Thymeleaf', 'Blade', 'Bootstrap', 'SCSS'],
            'DevOps et infrastructure' => ['Docker', 'Nginx', 'Git', 'GitLab CI/CD', 'GitHub Actions', 'Jenkins'],
            'Architecture et outils' => ['Redis', 'RabbitMQ', 'Kafka', 'Sentry', 'Composer', 'Maven', 'Vite'],
            'Pilotage' => ['Cadrage', 'planification', 'roadmap', 'backlog', 'priorisation', 'suivi d’avancement', 'analyse des besoins', 'faisabilité', 'spécifications fonctionnelles et techniques', 'Agile', 'Scrum', 'sprints', 'jalons', 'recette', 'amélioration continue', 'coordination des parties prenantes'],
        ];

        foreach (array_values($taxonomy) as $categoryIndex => $names) {
            $categoryName = array_keys($taxonomy)[$categoryIndex];
            $categoryKey = Str::slug($categoryName);
            $category = SkillCategory::query()->firstOrCreate(['key' => $categoryKey], [
                'status' => 'draft',
                'is_visible' => false,
                'sort_order' => ($categoryIndex + 1) * 10,
            ]);
            $category->translations()->firstOrCreate(['locale' => 'fr'], ['name' => $categoryName]);

            foreach ($names as $skillIndex => $name) {
                $key = Str::slug(str_replace(['/', '.'], ['-', ''], $name));
                $skill = Skill::query()->firstOrCreate(['key' => $key], [
                    'skill_category_id' => $category->getKey(),
                    'status' => 'draft',
                    'is_visible' => false,
                    'sort_order' => ($skillIndex + 1) * 10,
                ]);
                $skill->translations()->firstOrCreate(['locale' => 'fr'], ['name' => $name, 'description' => null]);
            }
        }
    }
}
