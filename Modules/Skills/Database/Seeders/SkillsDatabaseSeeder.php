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
            'Backend' => ['PHP', 'Laravel', 'Java', 'Spring Boot', 'API REST', 'MVC', 'POO', 'RBAC', 'Node.js', 'Express', 'Socket.IO', 'Développement back-end', 'Développement d’API', 'Authentification', 'Architecture logicielle', 'Conception de systèmes', 'Sécurité des applications web'],
            'Données' => ['MySQL', 'MariaDB', 'PostgreSQL', 'SQL', 'JPA/Hibernate', 'migrations', 'indexation', 'optimisation SQL', 'SQLite', 'Conception de bases de données'],
            'Tests et qualité' => ['PHPUnit', 'Playwright', 'JUnit', 'Mockito', 'PHPStan/Larastan', 'Postman', 'Laravel Pint', 'Tests logiciels automatisés'],
            'Frontend' => ['Vue.js', 'JavaScript', 'TypeScript', 'Thymeleaf', 'Blade', 'Bootstrap', 'SCSS', 'React', 'React Native', 'Expo', 'HTML5', 'CSS3', 'PWA', 'Leaflet', 'Développement web', 'Développement full-stack', 'Responsive design', 'Accessibilité web', 'Applications mobiles'],
            'DevOps et infrastructure' => ['Docker', 'Nginx', 'Git', 'GitLab CI/CD', 'GitHub Actions', 'Jenkins', 'YAML', 'Gestion des issues et branches', 'GitHub', 'CI/CD'],
            'Architecture et outils' => ['Redis', 'RabbitMQ', 'Kafka', 'Sentry', 'Composer', 'Maven', 'Vite'],
            'Jeu vidéo et 3D' => ['Unity', 'C#', 'Blender', 'Développement de jeux vidéo', 'Développement de jeux 3D', 'Programmation du gameplay', 'Mécaniques de jeu', 'Physique de jeu', 'Prototypage de jeux', 'Interface de jeu', 'Débogage de jeux', 'Level design', 'Modélisation 3D', 'Intégration d’assets 3D'],
            'Pilotage' => ['Cadrage', 'planification', 'roadmap', 'backlog', 'priorisation', 'suivi d’avancement', 'analyse des besoins', 'faisabilité', 'spécifications fonctionnelles et techniques', 'Agile', 'Scrum', 'sprints', 'jalons', 'recette', 'amélioration continue', 'coordination des parties prenantes', 'Gestion de projet', 'Gestion de projet agile', 'Livraison de projet', 'Coordination de projet', 'Collaboration d’équipe', 'Collaboration transverse', 'Communication client', 'Analyse technique', 'Résolution de problèmes'],
        ];

        foreach (array_values($taxonomy) as $categoryIndex => $names) {
            $categoryName = array_keys($taxonomy)[$categoryIndex];
            $categoryKey = Str::slug($categoryName);
            $category = SkillCategory::query()->firstOrCreate(['key' => $categoryKey], [
                'status' => 'published',
                'is_visible' => true,
                'published_at' => now(),
                'sort_order' => ($categoryIndex + 1) * 10,
            ]);
            $category->translations()->firstOrCreate(['locale' => 'fr'], ['name' => $categoryName]);

            foreach ($names as $skillIndex => $name) {
                $key = Str::slug(str_replace(['/', '.'], ['-', ''], $name));
                $skill = Skill::query()->firstOrCreate(['key' => $key], [
                    'skill_category_id' => $category->getKey(),
                    'status' => 'published',
                    'is_visible' => true,
                    'published_at' => now(),
                    'sort_order' => ($skillIndex + 1) * 10,
                ]);
                $skill->translations()->firstOrCreate(['locale' => 'fr'], ['name' => $name, 'description' => null]);
            }
        }
    }
}
