<?php

declare(strict_types=1);

namespace Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\Projects\Contracts\PublicProjectReader;

final class PublicSeoController extends Controller
{
    public function __construct(private readonly PublicProjectReader $projects) {}

    public function sitemap(): Response
    {
        $urls = [];
        foreach (['fr', 'en'] as $locale) {
            $urls[] = [
                'location' => route('portfolio.home', ['locale' => $locale]),
                'alternates' => $this->alternates('portfolio.home'),
            ];
            $urls[] = [
                'location' => route('portfolio.projects.index', ['locale' => $locale]),
                'alternates' => $this->alternates('portfolio.projects.index'),
            ];

            foreach ($this->projects->all($locale) as $project) {
                $urls[] = [
                    'location' => route('portfolio.projects.show', ['locale' => $locale, 'slug' => $project->slug]),
                    'alternates' => $this->projectAlternates($project->slug),
                ];
            }
        }

        return response(
            view('portfolio.seo.sitemap', ['urls' => $urls])->render(),
            200,
            ['Content-Type' => 'application/xml; charset=UTF-8'],
        );
    }

    public function robots(): Response
    {
        return response(implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /dashboard',
            'Disallow: /login',
            'Sitemap: '.route('portfolio.sitemap'),
            '',
        ]), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** @return array<string, string> */
    private function alternates(string $routeName): array
    {
        return [
            'fr' => route($routeName, ['locale' => 'fr']),
            'en' => route($routeName, ['locale' => 'en']),
        ];
    }

    /** @return array<string, string> */
    private function projectAlternates(string $slug): array
    {
        $alternates = [];
        foreach (['fr', 'en'] as $locale) {
            if ($this->projects->findBySlug($locale, $slug) !== null) {
                $alternates[$locale] = route('portfolio.projects.show', compact('locale', 'slug'));
            }
        }

        return $alternates;
    }
}
