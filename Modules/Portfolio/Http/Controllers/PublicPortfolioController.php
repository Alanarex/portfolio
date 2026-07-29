<?php

declare(strict_types=1);

namespace Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\Career\Contracts\PublicCareerReader;
use Modules\Career\Data\PublicCareerData;
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
        private readonly PublicSettingsReader $settings,
        private readonly PublicCareerReader $career,
        private readonly PublicSkillsReader $skills,
        private readonly PublicProjectReader $projects,
    ) {}

    public function home(string $locale): View
    {
        $this->activateLocale($locale);
        $data = $this->sharedData($locale);

        return view('portfolio.home', [
            ...$data,
            'page' => 'home',
            'alternateUrl' => route('portfolio.home', ['locale' => $data['alternateLocale']]),
            'meta' => $this->meta(
                title: __('portfolio.meta.home_title'),
                description: $data['profile']?->summary ?? __('portfolio.meta.home_description'),
                canonical: route('portfolio.home', ['locale' => $locale]),
            ),
        ]);
    }

    public function index(string $locale): View
    {
        $this->activateLocale($locale);
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
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $this->activateLocale($locale);
        $project = $this->projects->findBySlug($locale, $slug);
        abort_if($project === null, 404);
        $data = $this->sharedData($locale);

        return view('portfolio.projects.show', [
            ...$data,
            'page' => 'project',
            'project' => $project,
            'alternateUrl' => $this->alternateProjectUrl($data['alternateLocale'], $project->slug),
            'meta' => $this->meta(
                title: __('portfolio.meta.project_title', ['project' => $project->title]),
                description: $project->summary,
                canonical: route('portfolio.projects.show', ['locale' => $locale, 'slug' => $project->slug]),
            ),
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
     *   verifiedMetrics: list<string>
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
            'verifiedMetrics' => $this->verifiedMetrics($career),
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
}
