<?php

declare(strict_types=1);

namespace Modules\Projects\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Projects\Models\Project;

final class ProjectsDatabaseSeeder extends Seeder
{
    /**
     * Case studies written from each public README (features, architecture and limits as the README states them).
     * Nothing here is invented: unknown facts (role, metrics) are left out. Format: [type, order, heading, body];
     * lines starting with "- " in a body render as a list.
     */
    private const SECTIONS = [
        'strive' => [
            ['context', 10, 'Contexte', 'Application mobile de suivi d’activités sportives dans le style de Strava, réalisée dans un cadre scolaire avec React Native et Expo. Elle enregistre un parcours GPS, l’affiche sur une carte et calcule les statistiques de l’activité. Tout est stocké sur l’appareil, sans serveur.'],
            ['approach', 20, 'Fonctionnalités', "- Comptes locaux : inscription, connexion et session persistante (expo-secure-store).\n- Enregistrement d’une activité : démarrer, pause, reprise et arrêt, avec des points GPS échantillonnés par distance et par durée.\n- Suivi en arrière-plan écran éteint (nécessite un build de développement, pas Expo Go).\n- Carte du parcours (react-native-maps) et détail de chaque activité.\n- Statistiques : distance, durée, vitesse moyenne, statistiques par type et graphique en courbe.\n- Écran de succès personnels et notifications locales de paliers.\n- Export d’une activité en GPX ou CSV.\n- Édition du profil avec photo et gestion des permissions."],
            ['architecture', 30, 'Architecture', "Le code est organisé par responsabilité dans src/ :\n- components : éléments d’interface (carte, graphiques, panneaux de statistiques, badges).\n- screens : accueil, connexion, inscription, enregistrement, détail d’activité, classement, profil.\n- services : base SQLite, localisation, notifications et stockage d’arrière-plan.\n- context : AuthContext et RecordingContext.\n- hooks, utils (calculs GPS avec la formule de haversine, formats, export GPX/CSV, hachage du mot de passe) et types.\nLes données restent uniquement sur l’appareil, sans backend ni synchronisation."],
            ['results', 40, 'Ce que le projet démontre', "- Développement mobile multiplateforme avec React Native, Expo et TypeScript en mode strict.\n- Géolocalisation au premier plan et en arrière-plan, gestion des permissions et notifications locales.\n- Persistance locale avec SQLite et stockage sécurisé de la session.\n- Une architecture à base de contextes React avec React Navigation."],
            ['lessons', 50, 'Limites connues', 'Le projet n’a pas encore de tests automatisés : ils figurent dans les tickets ouverts du dépôt.'],
        ],
        'trip-flow' => [
            ['context', 10, 'Contexte', 'Application mobile de préparation de voyages construite avec Expo et React Native. On planifie un voyage, on ajoute des étapes sur une carte, on tient une checklist et un journal de bord. Comptes et données sont stockés localement dans SQLite, sans serveur.'],
            ['approach', 20, 'Fonctionnalités', "- Inscription et connexion locales avec session persistante (expo-secure-store).\n- Voyages : création, modification et suppression, avec dates (sélecteur de date) et image de couverture.\n- Étapes : ajout et édition avec un sélecteur de lieu ; les voyages s’affichent sur une carte avec marqueurs et tracé (react-native-maps).\n- Checklist par voyage.\n- Journal par voyage, avec photos (expo-image-picker, expo-image).\n- Profil utilisateur avec nom et prénom.\n- Données de démonstration injectées au premier lancement."],
            ['architecture', 30, 'Architecture', "Routes par fichiers avec Expo Router :\n- (tabs) : accueil, exploration (non finalisée) et profil.\n- auth : connexion, inscription, SessionProvider et dépôt local.\n- trips : liste, création, étapes, checklist et journal.\n- lib/db : connexion SQLite, migrations versionnées et utilitaires (hachage, uuid).\nLes données vivent dans une base SQLite sur l’appareil (utilisateurs, voyages, étapes, journaux, checklists et éléments de checklist). Il n’y a pas de backend."],
            ['results', 40, 'Ce que le projet démontre', "- Développement mobile multiplateforme avec Expo Router et TypeScript strict.\n- Approche local-first : schéma SQLite, migrations versionnées et modules de dépôt.\n- Cartes, choix d’images, saisie de dates et de lieux.\n- Un fournisseur de session et un parcours d’authentification simple."],
            ['lessons', 50, 'Limites connues', 'Pas encore de tests automatisés (tickets ouverts sur le dépôt). Les cartes demandent un build de développement ou des clés de plateforme pour un usage en production.'],
        ],
        'trixscore' => [
            ['context', 10, 'Contexte', 'Application web progressive pensée pour mobile, qui compte les points au Trex, un jeu de cartes à 4 joueurs. Elle est écrite en JavaScript pur, sans framework ni outil de build ; les données restent dans le navigateur (localStorage).'],
            ['approach', 20, 'Fonctionnalités', "- Modes individuel (4 joueurs) et par équipes (2 contre 2).\n- Un formulaire de saisie dédié par contrat : Trex, Dames, Roi, Carreaux et Plis.\n- Tableau des scores en direct avec totaux cumulés et détail par manche.\n- Historique des parties enregistré dans le navigateur.\n- Barèmes modifiables, avec retour aux valeurs par défaut.\n- Thème clair ou sombre, compteurs et sélecteurs adaptés au tactile.\n- PWA installable avec service worker pour un usage hors ligne."],
            ['architecture', 30, 'Règles et architecture', "Une partie compte 20 manches : 4 royaumes de 5 manches, chacune jouée avec un contrat. Les barèmes par défaut sont dans src/core/settings.js.\nLe code utilise des balises script classiques partageant un état global, sans bundler :\n- src/core : constantes, historique, calcul des scores, réglages.\n- src/components : modale, barre de navigation, compteur de score.\n- src/helpers et src/screens : aides DOM, formulaires de contrat, écrans.\n- css/{core,components,screens}, service-worker.js et manifest.json pour l’installation."],
            ['results', 40, 'Ce que le projet démontre', "- Une application complète en JavaScript sans framework, organisée en modules par responsabilité.\n- Une logique métier de calcul de scores (individuel, équipes, doublés et bonus).\n- Les bases d’une PWA : manifeste, installation et cache par service worker.\n- Une interface mobile-first avec thèmes via variables CSS."],
            ['lessons', 50, 'Limites connues', 'Pas encore de tests automatisés (tickets ouverts sur le dépôt).'],
        ],
        'pwa-camera' => [
            ['context', 10, 'Contexte', 'Application web progressive en React et TypeScript accompagnée d’un petit serveur Express et Socket.IO. Elle permet de prendre des photos, de partager sa position et de discuter en temps réel. Une démonstration hébergée par l’école existe, mais elle peut être hors ligne.'],
            ['approach', 20, 'Fonctionnalités', "- Appareil photo : capture avec getUserMedia, photos conservées dans le localStorage du navigateur.\n- Galerie des photos prises.\n- Messagerie temps réel via Socket.IO ; les noms d’utilisateur sont uniques parmi les connectés.\n- Position géographique affichée sur une carte Leaflet.\n- Écrans d’appel qui ouvrent le gestionnaire tel:.\n- PWA installable, service worker généré par vite-plugin-pwa."],
            ['architecture', 30, 'Architecture', "- src : interface React 18 et React Router 6 (pages, composants, LocationContext, client Socket.IO partagé), construite avec Vite et servie sous le chemin /typescript_camera.\n- server/server.js : serveur Express avec Socket.IO. Messages et utilisateurs connectés sont gardés en mémoire (rien n’est persisté) et quelques routes REST les exposent.\n- La configuration vient d’un fichier .env.\nLe déploiement sur VPS derrière Apache avec proxy WebSocket et PM2 est décrit dans la documentation du dépôt."],
            ['results', 40, 'Ce que le projet démontre', "- Des fonctions temps réel avec Socket.IO et un contexte client partagé dans React.\n- Les API du navigateur : caméra, géolocalisation et service worker.\n- Du code React typé avec TypeScript et Vite.\n- Un déploiement derrière Apache avec proxy WebSocket et PM2."],
            ['lessons', 50, 'Limites connues', 'Le serveur n’a pas d’authentification et son état est en mémoire : le projet convient à une démonstration, pas à un usage réel. Pas de tests automatisés pour l’instant.'],
        ],
        'github-actions' => [
            ['context', 10, 'Contexte', 'Un modèle de dépôt réutilisable : un formulaire d’issue et 4 workflows GitHub Actions qui transforment une nouvelle issue en tâche titrée, étiquetée, assignée et dotée de sa propre branche de release. Il n’y a aucun code applicatif : il suffit de copier le dossier .github dans un autre dépôt.'],
            ['approach', 20, 'Ce que font les workflows', "Une issue créée depuis le formulaire (.github/ISSUE_TEMPLATE/issue.yml) déclenche quatre workflows à l’ouverture et à la modification :\n- issue-title : préfixe le titre avec le type, par exemple [REFACTOR].\n- issue-labels : crée au besoin le label de type (rouge) et celui de version (bleu), puis pose exactement ces deux labels.\n- issue-assign : assigne l’auteur si la case « m’assigner directement » est cochée.\n- issue-branch : si la case est cochée, crée la branche release/<version>/<type>/<titre> et commente avec les instructions de checkout.\nLes issues qui ne viennent pas du formulaire sont ignorées et les issues vides sont désactivées."],
            ['architecture', 30, 'Utilisation', "Le formulaire propose neuf types (feature, bug, chore, docs, enhancement, refactor, test, perf, security) et une version cible au format vX.Y.Z.\nPour l’utiliser dans un autre dépôt :\n- copier le dossier .github ;\n- donner aux Actions les droits de lecture et d’écriture (Settings, Actions, Workflow permissions) ;\n- ouvrir une nouvelle issue avec le formulaire."],
            ['results', 40, 'Ce que le projet démontre', "- De l’automatisation événementielle avec GitHub Actions et actions/github-script.\n- Des permissions au moindre privilège, workflow par workflow.\n- Des formulaires d’issues en YAML et une convention de nommage des branches liée aux versions de release."],
            ['lessons', 50, 'Limites connues', 'Les workflows n’ont pas de tests automatisés : ils se vérifient en créant une issue depuis le formulaire.'],
        ],
    ];

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
                'start' => [2026, 1],
                'lifecycle' => 'completed',
                'repo' => 'https://github.com/Alanarex/strive',
                'skills' => ['typescript', 'react-native', 'expo', 'react', 'sqlite'],
                'demo' => null,
            ],
            [
                'slug' => 'trip-flow',
                'summary' => 'Application mobile de préparation de voyages : voyages, étapes géolocalisées sur carte, checklist et journal avec photos, données locales en SQLite.',
                'technologies' => ['React Native', 'Expo', 'TypeScript', 'SQLite'],
                'featured' => true,
                'order' => 20,
                'start' => [2025, 9],
                'lifecycle' => 'completed',
                'repo' => 'https://github.com/Alanarex/trip-flow-1',
                'skills' => ['typescript', 'react-native', 'expo', 'react', 'sqlite'],
                'demo' => null,
            ],
            [
                'slug' => 'trixscore',
                'summary' => 'Application web progressive pour compter les points au Trex : modes individuel et équipes, historique, barèmes modifiables, hors ligne. JavaScript pur, sans build.',
                'technologies' => ['JavaScript', 'HTML', 'CSS', 'PWA'],
                'featured' => true,
                'order' => 30,
                'start' => [2026, 1],
                'lifecycle' => 'completed',
                'repo' => 'https://github.com/Alanarex/TrixScore',
                'demo' => 'https://alanarex.github.io/TrixScore/',
                'skills' => ['javascript', 'html5', 'css3', 'pwa'],
            ],
            [
                'slug' => 'pwa-camera',
                'summary' => 'Application web progressive : prise de photos, géolocalisation sur carte et messagerie temps réel via un serveur Express + Socket.IO.',
                'technologies' => ['React', 'TypeScript', 'Vite', 'Express', 'Socket.IO', 'Leaflet'],
                'featured' => false,
                'order' => 110,
                'start' => [2025, 1],
                'lifecycle' => 'completed',
                'repo' => 'https://github.com/Alanarex/typescript_camera',
                'skills' => ['typescript', 'react', 'vite', 'nodejs', 'express', 'socketio', 'pwa', 'leaflet'],
                'demo' => null,
            ],
            [
                'slug' => 'github-actions',
                'summary' => 'Modèle réutilisable : un formulaire d’issue et 4 workflows GitHub Actions qui titrent, étiquettent, assignent l’issue et créent sa branche de release.',
                'technologies' => ['GitHub Actions', 'YAML'],
                'featured' => false,
                'order' => 130,
                'start' => [2025, 11],
                'lifecycle' => 'completed',
                'repo' => 'https://github.com/Alanarex/github-actions',
                'skills' => ['github-actions', 'yaml', 'git', 'gestion-des-issues-et-branches'],
                'demo' => null,
            ],
        ];

        foreach ($projects as $row) {
            $project = Project::query()->firstOrCreate(['slug' => $row['slug']], [
                'uuid' => (string) Str::uuid(),
                'lifecycle_status' => $row['lifecycle'],
                'technologies' => $row['technologies'],
                'start_year' => $row['start'][0],
                'start_month' => $row['start'][1],
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
            $this->sections($project, self::SECTIONS[$row['slug']]);
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

    /** @param list<array{string, int, string, string}> $sections */
    private function sections(Project $project, array $sections): void
    {
        foreach ($sections as [$type, $order, $heading, $body]) {
            $section = $project->caseStudySections()->firstOrCreate(['type' => $type], [
                'is_public' => true,
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
            'trixscore' => 'TrixScore',
            'trip-flow' => 'Trip Flow',
            'pwa-camera' => 'PWA Camera',
            'github-actions' => 'GitHub Actions workflows',
            default => Str::headline($slug),
        };
    }
}
