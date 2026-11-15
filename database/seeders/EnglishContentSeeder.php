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
 * Called by DatabaseSeeder, so `php artisan migrate:fresh --seed` also creates the profile and the English
 * translations. It can still be run alone: php artisan db:seed --class=EnglishContentSeeder --force.
 *
 * Adds the profile (FR + EN) and the English translations of the French content created by the
 * other seeders. Idempotent (firstOrCreate), never overwrites what was edited in the dashboard, and
 * keeps the published state of the French source records (it only adds translations).
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
            'is_published' => true,
            'show_location' => false,
            'show_availability' => false,
            'published_at' => now(),
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
        foreach (['revesta' => 'REVESTA: the extension that identifies the renovation grants you qualify for.', 'cojeco' => 'CoJeCo: Collectif des jeunes connectés.'] as $key => $summary) {
            $this->translate(Experience::query()->where('key', $key)->first(), [
                'role' => 'Co-founder',
                'employment_type' => null,
                'location' => 'Angers, France (hybrid)',
                'summary' => $summary,
                'highlights' => [],
            ]);
        }
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
            'universite-angers-bac-2' => ['program' => 'Computer programming, general programme', 'level' => 'Bac+2 (two-year university degree)', 'location' => null],
        ];
        foreach ($education as $key => $fields) {
            $this->translate(EducationRecord::query()->where('key', $key)->first(), [...$fields, 'summary' => null]);
        }

        Certification::query()->where('key', 'toeic-2026')->first()?->translations()->firstOrCreate(['locale' => 'en'], [
            'name' => 'TOEIC',
            'skill_label' => 'Professional English',
        ]);

        Certification::query()->where('key', 'unity-junior-programmer')->first()?->translations()->firstOrCreate(['locale' => 'en'], [
            'name' => 'Unity Junior Programmer',
            'skill_label' => 'Game development with Unity and C#',
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
            'jeu-video-et-3d' => 'Video games and 3D',
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
            'Développement back-end' => 'Back-end development',
            'Développement d’API' => 'API development',
            'Authentification' => 'Authentication',
            'Architecture logicielle' => 'Software architecture',
            'Conception de systèmes' => 'Systems design',
            'Sécurité des applications web' => 'Web application security',
            'Conception de bases de données' => 'Database design',
            'Tests logiciels automatisés' => 'Automated software testing',
            'Développement web' => 'Web development',
            'Développement full-stack' => 'Full-stack development',
            'Responsive design' => 'Responsive web design',
            'Accessibilité web' => 'Web accessibility',
            'Applications mobiles' => 'Mobile applications',
            'Développement de jeux vidéo' => 'Game development',
            'Développement de jeux 3D' => '3D game development',
            'Programmation du gameplay' => 'Gameplay programming',
            'Mécaniques de jeu' => 'Game mechanics',
            'Physique de jeu' => 'Game physics',
            'Prototypage de jeux' => 'Game prototyping',
            'Interface de jeu' => 'Game UI development',
            'Débogage de jeux' => 'Game debugging',
            'Level design' => 'Level design',
            'Modélisation 3D' => '3D modeling',
            'Intégration d’assets 3D' => '3D asset integration',
            'Gestion de projet' => 'Project management',
            'Gestion de projet agile' => 'Agile project management',
            'Livraison de projet' => 'Project delivery',
            'Coordination de projet' => 'Project coordination',
            'Collaboration d’équipe' => 'Team collaboration',
            'Collaboration transverse' => 'Cross-functional collaboration',
            'Communication client' => 'Client communication',
            'Analyse technique' => 'Technical analysis',
            'Résolution de problèmes' => 'Problem solving',
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
            'strive' => [
                'context' => ['Context', 'Mobile app for tracking sports activities in the style of Strava, built for school with React Native and Expo. It records a GPS route, shows it on a map and computes the activity statistics. Everything is stored on the device, with no server.'],
                'approach' => ['Features', "- Local accounts: sign-up, sign-in and a persistent session (expo-secure-store).\n- Recording an activity: start, pause, resume and stop, with GPS points sampled by distance and duration.\n- Background tracking with the screen off (needs a development build, not Expo Go).\n- Route map (react-native-maps) and a detail view for each activity.\n- Statistics: distance, duration, average speed, statistics by type and a line chart.\n- Personal achievements screen and local milestone notifications.\n- Export of an activity as GPX or CSV.\n- Profile editing with a photo and permission handling."],
                'architecture' => ['Architecture', "The code is organised by responsibility in src/:\n- components: interface elements (map, charts, statistics panels, badges).\n- screens: home, sign-in, sign-up, recording, activity detail, leaderboard, profile.\n- services: SQLite database, location, notifications and background storage.\n- context: AuthContext and RecordingContext.\n- hooks, utils (GPS calculations with the haversine formula, formats, GPX/CSV export, password hashing) and types.\nData stays on the device only, with no backend and no synchronisation."],
                'results' => ['What the project demonstrates', "- Cross-platform mobile development with React Native, Expo and strict TypeScript.\n- Foreground and background geolocation, permission handling and local notifications.\n- Local persistence with SQLite and secure storage of the session.\n- An architecture based on React contexts with React Navigation."],
                'lessons' => ['Known limits', 'The project has no automated tests yet: they are listed in the repository’s open issues.'],
            ],
            'trip-flow' => [
                'context' => ['Context', 'Mobile trip-planning app built with Expo and React Native. You plan a trip, add stops on a map, keep a checklist and a travel journal. Accounts and data are stored locally in SQLite, with no server.'],
                'approach' => ['Features', "- Local sign-up and sign-in with a persistent session (expo-secure-store).\n- Trips: create, edit and delete, with dates (date picker) and a cover image.\n- Stops: add and edit with a place picker; trips appear on a map with markers and a route (react-native-maps).\n- A checklist for each trip.\n- A journal for each trip, with photos (expo-image-picker, expo-image).\n- User profile with first and last name.\n- Demo data injected on first launch."],
                'architecture' => ['Architecture', "File-based routes with Expo Router:\n- (tabs): home, explore (not finished) and profile.\n- auth: sign-in, sign-up, SessionProvider and local repository.\n- trips: list, creation, stops, checklist and journal.\n- lib/db: SQLite connection, versioned migrations and utilities (hashing, uuid).\nData lives in a SQLite database on the device (users, trips, stops, journals, checklists and checklist items). There is no backend."],
                'results' => ['What the project demonstrates', "- Cross-platform mobile development with Expo Router and strict TypeScript.\n- A local-first approach: SQLite schema, versioned migrations and repository modules.\n- Maps, image picking, and date and place input.\n- A session provider and a simple authentication flow."],
                'lessons' => ['Known limits', 'No automated tests yet (open issues on the repository). Maps need a development build or platform keys for production use.'],
            ],
            'trixscore' => [
                'context' => ['Context', 'Mobile-first progressive web app that keeps score for Trex, a 4-player card game. It is written in plain JavaScript, with no framework or build tool; data stays in the browser (localStorage).'],
                'approach' => ['Features', "- Individual (4 players) and team (2 against 2) modes.\n- A dedicated entry form for each contract: Trex, Queens, King, Diamonds and Tricks.\n- Live scoreboard with running totals and a breakdown by round.\n- Game history saved in the browser.\n- Editable scoring rules, with a reset to the default values.\n- Light or dark theme, with touch-friendly counters and selectors.\n- Installable PWA with a service worker for offline use."],
                'architecture' => ['Rules and architecture', "A game has 20 rounds: 4 kingdoms of 5 rounds, each played with one contract. The default scoring is in src/core/settings.js.\nThe code uses classic script tags sharing a global state, with no bundler:\n- src/core: constants, history, score calculation, settings.\n- src/components: modal, navigation bar, score counter.\n- src/helpers and src/screens: DOM helpers, contract forms, screens.\n- css/{core,components,screens}, service-worker.js and manifest.json for installation."],
                'results' => ['What the project demonstrates', "- A complete application in plain JavaScript without a framework, organised into modules by responsibility.\n- Business logic for score calculation (individual, teams, doubles and bonuses).\n- The basics of a PWA: manifest, installation and service-worker caching.\n- A mobile-first interface with themes through CSS variables."],
                'lessons' => ['Known limits', 'No automated tests yet (open issues on the repository).'],
            ],
            'pwa-camera' => [
                'context' => ['Context', 'Progressive web app in React and TypeScript with a small Express and Socket.IO server. It lets you take photos, share your location and chat in real time. A demo hosted by the school exists, but it may be offline.'],
                'approach' => ['Features', "- Camera: capture with getUserMedia, photos kept in the browser’s localStorage.\n- Gallery of the photos taken.\n- Real-time messaging through Socket.IO; user names are unique among connected users.\n- Geographic position shown on a Leaflet map.\n- Call screens that open the tel: handler.\n- Installable PWA, with a service worker generated by vite-plugin-pwa."],
                'architecture' => ['Architecture', "- src: React 18 and React Router 6 interface (pages, components, LocationContext, shared Socket.IO client), built with Vite and served under the /typescript_camera path.\n- server/server.js: Express server with Socket.IO. Messages and connected users are kept in memory (nothing is persisted) and a few REST routes expose them.\n- Configuration comes from an .env file.\nDeployment on a VPS behind Apache with a WebSocket proxy and PM2 is described in the repository documentation."],
                'results' => ['What the project demonstrates', "- Real-time features with Socket.IO and a shared client context in React.\n- Browser APIs: camera, geolocation and service worker.\n- React code typed with TypeScript and Vite.\n- A deployment behind Apache with a WebSocket proxy and PM2."],
                'lessons' => ['Known limits', 'The server has no authentication and its state is in memory: the project suits a demonstration, not real use. No automated tests for now.'],
            ],
            'github-actions' => [
                'context' => ['Context', 'A reusable repository template: an issue form and 4 GitHub Actions workflows that turn a new issue into a task with a title, labels, an assignee and its own release branch. There is no application code: you just copy the .github folder into another repository.'],
                'approach' => ['What the workflows do', "An issue created from the form (.github/ISSUE_TEMPLATE/issue.yml) triggers four workflows when it is opened and when it is edited:\n- issue-title: prefixes the title with the type, for example [REFACTOR].\n- issue-labels: creates the type label (red) and the version label (blue) if needed, then applies exactly those two labels.\n- issue-assign: assigns the author if the “assign me directly” box is ticked.\n- issue-branch: if the box is ticked, creates the branch release/<version>/<type>/<title> and comments with the checkout instructions.\nIssues that do not come from the form are ignored and blank issues are disabled."],
                'architecture' => ['Usage', "The form offers nine types (feature, bug, chore, docs, enhancement, refactor, test, perf, security) and a target version in the vX.Y.Z format.\nTo use it in another repository:\n- copy the .github folder;\n- give Actions read and write permissions (Settings, Actions, Workflow permissions);\n- open a new issue with the form."],
                'results' => ['What the project demonstrates', "- Event-driven automation with GitHub Actions and actions/github-script.\n- Least-privilege permissions, workflow by workflow.\n- YAML issue forms and a branch naming convention tied to release versions."],
                'lessons' => ['Known limits', 'The workflows have no automated tests: they are checked by creating an issue from the form.'],
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
