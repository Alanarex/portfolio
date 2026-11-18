<?php

declare(strict_types=1);

namespace Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Career\Contracts\PublicCareerReader;
use Modules\Career\Data\PublicCareerData;
use Modules\GitHubActivity\Contracts\PublicContributionReader;
use Modules\GitHubActivity\Contracts\PublicGitHubActivityReader;
use Modules\GitHubActivity\Data\PublicGitHubActivityData;
use Modules\Portfolio\Contracts\PublicAnalytics;
use Modules\Portfolio3D\Contracts\PublicSceneReader;
use Modules\Portfolio3D\Data\SceneManifestData;
use Modules\Posts\Contracts\PublicPostsReader;
use Modules\Profile\Contracts\PublicCvReader;
use Modules\Profile\Contracts\PublicProfileReader;
use Modules\Profile\Data\PublicProfileData;
use Modules\Projects\Contracts\PublicProjectReader;
use Modules\Projects\Data\PublicProjectData;
use Modules\Settings\Contracts\PublicSettingsReader;
use Modules\Settings\Data\PublicSettingsData;
use Modules\Skills\Contracts\PublicSkillsReader;
use Modules\Skills\Data\PublicSkillsData;

final class PublicPortfolioController extends Controller
{
    public function __construct(
        private readonly PublicProfileReader $profiles,
        private readonly PublicCvReader $cv,
        private readonly PublicSettingsReader $settings,
        private readonly PublicCareerReader $career,
        private readonly PublicSkillsReader $skills,
        private readonly PublicProjectReader $projects,
        private readonly PublicAnalytics $analytics,
        private readonly PublicPostsReader $posts,
        private readonly PublicGitHubActivityReader $github,
        private readonly PublicContributionReader $contributions,
        private readonly PublicSceneReader $scenes,
    ) {}

    public function home(string $locale): View
    {
        $this->activateLocale($locale);
        $this->analytics->pageViewed('portfolio.home', $locale);
        $data = $this->sharedData($locale);

        return view('portfolio.home', [
            ...$data,
            'page' => 'home',
            'scene' => $this->scene($locale, $data['settings']),
            'posts' => $this->posts->latest($locale),
            'contributionGrid' => ($data['settings']?->featureFlags['activity'] ?? false) === true ? $this->contributions->grid() : null,
            'githubActivity' => ($data['settings']?->featureFlags['activity'] ?? false) === true ? $this->publicActivity($locale) : null,
            'alternateUrl' => route('portfolio.home', ['locale' => $data['alternateLocale']]),
            'meta' => $this->meta(
                title: __('portfolio.meta.home_title'),
                description: $data['profile']->summary ?? __('portfolio.meta.home_description'),
                canonical: route('portfolio.home', ['locale' => $locale]),
            ),
            'structuredData' => $this->structuredData($data, route('portfolio.home', ['locale' => $locale]), __('portfolio.meta.home_title'), $this->homeNavigation($locale)),
        ]);
    }

    /**
     * GitHub activity limited to the repositories behind published projects: client, company and unfinished
     * repositories never leak into the public feed.
     */
    private function publicActivity(string $locale): ?PublicGitHubActivityData
    {
        $activity = $this->github->current();
        if ($activity === null) {
            return null;
        }

        $allowed = [];
        foreach ($this->projects->all($locale) as $project) {
            if ($project->repositoryUrl !== null) {
                $allowed[strtolower(basename(rtrim($project->repositoryUrl, '/')))] = $project->slug;
            }
        }

        $repositories = [];
        foreach ($activity->repositories as $repository) {
            $key = strtolower($repository['name']);
            if (isset($allowed[$key])) {
                $repository['project_slug'] = $allowed[$key];
                $repositories[] = $repository;
            }
        }
        $events = array_values(array_filter(
            $activity->events,
            fn (array $event): bool => isset($allowed[strtolower(basename($event['repo']))]) && $event['type'] !== '',
        ));

        return new PublicGitHubActivityData($activity->username, $repositories, $events, $activity->syncedAt);
    }

    public function index(string $locale): View
    {
        $this->activateLocale($locale);
        $this->analytics->pageViewed('portfolio.projects.index', $locale);
        $data = $this->sharedData($locale);

        return view('portfolio.projects.index', [
            ...$data,
            'page' => 'projects',
            'projects' => $this->projects->all($locale),
            'alternateUrl' => route('portfolio.projects.index', ['locale' => $data['alternateLocale']]),
            'meta' => $this->meta(
                title: __('portfolio.meta.projects_title'),
                description: __('portfolio.meta.projects_description'),
                canonical: route('portfolio.projects.index', ['locale' => $locale]),
            ),
            'structuredData' => $this->structuredData($data, route('portfolio.projects.index', ['locale' => $locale]), __('portfolio.meta.projects_title'), [], [
                ['name' => __('portfolio.seo.breadcrumb_home'), 'url' => route('portfolio.home', ['locale' => $locale])],
                ['name' => __('portfolio.nav.projects'), 'url' => route('portfolio.projects.index', ['locale' => $locale])],
            ]),
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $this->activateLocale($locale);
        $this->analytics->pageViewed('portfolio.projects.show', $locale);
        $project = $this->projects->findBySlug($locale, $slug);
        abort_if($project === null, 404);
        $data = $this->sharedData($locale);
        $slugs = array_map(static fn (PublicProjectData $item): string => $item->slug, $this->projects->all($locale));
        $position = array_search($project->slug, $slugs, true);
        $neighbours = $this->projects->all($locale);

        return view('portfolio.projects.show', [
            ...$data,
            'page' => 'project',
            'project' => $project,
            'previousProject' => is_int($position) ? ($neighbours[$position - 1] ?? null) : null,
            'nextProject' => is_int($position) ? ($neighbours[$position + 1] ?? null) : null,
            'alternateUrl' => $this->alternateProjectUrl($data['alternateLocale'], $project->slug),
            'meta' => $this->meta(
                title: __('portfolio.meta.project_title', ['project' => $project->title]),
                description: $project->summary,
                canonical: route('portfolio.projects.show', ['locale' => $locale, 'slug' => $project->slug]),
            ),
            'structuredData' => $this->structuredData($data, route('portfolio.projects.show', ['locale' => $locale, 'slug' => $project->slug]), __('portfolio.meta.project_title', ['project' => $project->title]), [], [
                ['name' => __('portfolio.seo.breadcrumb_home'), 'url' => route('portfolio.home', ['locale' => $locale])],
                ['name' => __('portfolio.nav.projects'), 'url' => route('portfolio.projects.index', ['locale' => $locale])],
                ['name' => $project->title, 'url' => route('portfolio.projects.show', ['locale' => $locale, 'slug' => $project->slug])],
            ]),
        ]);
    }

    public function privacy(string $locale): View
    {
        $this->activateLocale($locale);
        $this->analytics->pageViewed('portfolio.privacy', $locale);
        $data = $this->sharedData($locale);
        $canonical = route('portfolio.privacy', compact('locale'));

        return view('portfolio.privacy', [
            ...$data,
            'page' => 'privacy',
            'alternateUrl' => route('portfolio.privacy', ['locale' => $data['alternateLocale']]),
            'meta' => $this->meta(
                title: __('portfolio.meta.privacy_title'),
                description: __('portfolio.meta.privacy_description'),
                canonical: $canonical,
            ),
            'structuredData' => $this->structuredData($data, $canonical, __('portfolio.meta.privacy_title')),
        ]);
    }

    public function legal(string $locale): View
    {
        $this->activateLocale($locale);
        $this->analytics->pageViewed('portfolio.legal', $locale);
        $data = $this->sharedData($locale);
        $canonical = route('portfolio.legal', compact('locale'));

        return view('portfolio.legal', [
            ...$data,
            'page' => 'legal',
            'legal' => config('legal'),
            'alternateUrl' => route('portfolio.legal', ['locale' => $data['alternateLocale']]),
            'meta' => $this->meta(
                title: __('portfolio.meta.legal_title'),
                description: __('portfolio.meta.legal_description'),
                canonical: $canonical,
            ),
            'structuredData' => $this->structuredData($data, $canonical, __('portfolio.meta.legal_title')),
        ]);
    }

    /**
     * @return array{
     *   locale: string,
     *   alternateLocale: string,
     *   profile: PublicProfileData|null,
     *   settings: PublicSettingsData|null,
     *   career: PublicCareerData|null,
     *   skills: PublicSkillsData|null,
     *   featuredProjects: list<PublicProjectData>,
     *   projectsCount: int,
     *   allProjects: list<PublicProjectData>,
     *   verifiedMetrics: list<string>,
     *   cvAvailable: bool
     * }
     */
    private function sharedData(string $locale): array
    {
        $career = $this->career->forLocale($locale);
        $allProjects = $this->projects->all($locale);

        return [
            'locale' => $locale,
            'alternateLocale' => $locale === 'fr' ? 'en' : 'fr',
            'profile' => $this->profiles->forLocale($locale),
            'settings' => $this->settings->forLocale($locale),
            'career' => $career,
            'skills' => $this->skills->forLocale($locale),
            'featuredProjects' => $this->projects->featured($locale),
            'projectsCount' => count($allProjects),
            'allProjects' => $allProjects,
            'verifiedMetrics' => $this->verifiedMetrics($career),
            'cvAvailable' => $this->cv->available($locale),
        ];
    }

    /** @return list<string> */
    private function verifiedMetrics(?PublicCareerData $career): array
    {
        if ($career === null) {
            return [];
        }

        $metrics = [];
        foreach ($career->experiences as $experience) {
            foreach (($experience['achievements'] ?? []) as $achievement) {
                if (($achievement['is_quantified'] ?? false) === true && is_string($achievement['statement'] ?? null)) {
                    $metrics[] = $achievement['statement'];
                }
            }
        }

        return array_slice($metrics, 0, 4);
    }

    /** @return array{title: string, description: string, canonical: string} */
    private function meta(string $title, string $description, string $canonical): array
    {
        return compact('title', 'description', 'canonical');
    }

    /**
     * Balisage schema.org : WebSite (nom du site pour Google), Person, WebPage, navigation et fil d'Ariane.
     * Le WebSite et la Person pointent vers la racine du domaine, qui est l'identité unique du site (FR et EN).
     *
     * @param array{
     *   locale: string,
     *   profile: PublicProfileData|null,
     *   settings: PublicSettingsData|null
     * } $data
     * @param  list<array{name: string, url: string}>  $navigation
     * @param  list<array{name: string, url: string}>  $breadcrumbs
     * @return array<string, mixed>
     */
    private function structuredData(array $data, string $url, string $title, array $navigation = [], array $breadcrumbs = []): array
    {
        $settings = $data['settings'];
        $root = rtrim(route('portfolio.x-default'), '/').'/';
        $siteName = $settings instanceof PublicSettingsData ? $settings->siteName : __('portfolio.brand_fallback');
        $alternateNames = array_values(array_filter(
            (array) __('portfolio.seo.alternate_names'),
            static fn (mixed $name): bool => is_string($name) && $name !== $siteName,
        ));
        $person = ['@id' => $root.'#person'];

        $graph = [[
            '@type' => 'WebSite',
            '@id' => $root.'#website',
            'url' => $root,
            'name' => $siteName,
            'alternateName' => $alternateNames,
            'inLanguage' => ['fr', 'en'],
            'publisher' => $person,
        ]];

        $graph[] = [
            '@type' => 'WebPage',
            '@id' => $url.'#webpage',
            'url' => $url,
            'name' => $title,
            'inLanguage' => $data['locale'],
            'isPartOf' => ['@id' => $root.'#website'],
            'about' => $person,
        ];

        if ($data['profile'] instanceof PublicProfileData) {
            $graph[] = array_filter([
                '@type' => 'Person',
                '@id' => $root.'#person',
                'name' => $data['profile']->displayName,
                'url' => $root,
                'image' => asset('assets/brand/portrait-hero-640.webp'),
                'jobTitle' => $data['profile']->professionalTitles[0] ?? null,
                'description' => $data['profile']->summary,
                'sameAs' => $settings instanceof PublicSettingsData ? array_values($settings->socialLinks) : [],
            ], static fn (mixed $value): bool => $value !== null && $value !== []);
        }

        foreach ($navigation as $item) {
            $graph[] = ['@type' => 'SiteNavigationElement', 'name' => $item['name'], 'url' => $item['url']];
        }

        if ($breadcrumbs !== []) {
            $graph[] = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => array_map(
                    static fn (array $crumb, int $index): array => [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $crumb['name'],
                        'item' => $crumb['url'],
                    ],
                    $breadcrumbs,
                    array_keys($breadcrumbs),
                ),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    /** @return list<array{name: string, url: string}> */
    private function homeNavigation(string $locale): array
    {
        $home = route('portfolio.home', ['locale' => $locale]);

        $settings = $this->settings->forLocale($locale);
        $items = [];
        foreach (['projets' => 'projects', 'experience' => 'experience', 'competences' => 'skills', 'contact' => 'contact'] as $anchor => $key) {
            if ($settings?->sectionVisible($anchor) ?? true) {
                $items[] = ['name' => __('portfolio.nav.'.$key), 'url' => $home.'#'.$anchor];
            }
        }
        $items[] = ['name' => __('portfolio.footer.legal'), 'url' => route('portfolio.legal', ['locale' => $locale])];

        return $items;
    }

    private function activateLocale(string $locale): void
    {
        abort_unless(in_array($locale, ['fr', 'en'], true), 404);
        app()->setLocale($locale);
    }

    private function alternateProjectUrl(string $locale, string $slug): string
    {
        if ($this->projects->findBySlug($locale, $slug) === null) {
            return route('portfolio.projects.index', ['locale' => $locale]);
        }

        return route('portfolio.projects.show', ['locale' => $locale, 'slug' => $slug]);
    }

    private function scene(string $locale, ?PublicSettingsData $settings): ?SceneManifestData
    {
        $manifest = $this->scenes->forLocale($locale);
        if (! $manifest->enabled) {
            return null;
        }

        // Une cible absente de la page (section désactivée) ne doit pas produire de lien mort.
        $manifest = ($settings?->featureFlags['activity'] ?? false) === true ? $manifest : $manifest->withoutTargets(['activite']);

        return $manifest->hotspots === [] ? null : $manifest;
    }
}
