<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SiteNameStructuredDataTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function graph(string $uri): array
    {
        $html = $this->get($uri)->assertOk()->getContent();
        self::assertSame(1, preg_match('#<script type="application/ld\+json">(.+?)</script>#s', (string) $html, $m));
        $data = json_decode($m[1], true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('https://schema.org', $data['@context']);

        $byType = [];
        foreach ($data['@graph'] as $node) {
            $byType[$node['@type']][] = $node;
        }

        return $byType;
    }

    public function test_home_declares_the_site_name_on_the_domain_root_in_both_locales(): void
    {
        foreach (['/fr', '/en'] as $uri) {
            $graph = $this->graph($uri);
            $site = $graph['WebSite'][0];

            self::assertSame(rtrim(route('portfolio.x-default'), '/').'/', $site['url']);
            self::assertNotSame('', $site['name']);
            self::assertNotContains($site['name'], $site['alternateName']);
            self::assertContains('alaakhalil.dev', $site['alternateName']);
            self::assertSame(['fr', 'en'], $site['inLanguage']);
            self::assertCount(1, $graph['WebPage']);
            self::assertSame(url($uri), $graph['WebPage'][0]['url']);
        }
    }

    public function test_home_exposes_explicit_navigation_elements_for_sitelinks(): void
    {
        $names = array_column($this->graph('/fr')['SiteNavigationElement'], 'url', 'name');

        self::assertSame(route('portfolio.home', 'fr').'#experience', $names['Parcours']);
        self::assertSame(route('portfolio.home', 'fr').'#competences', $names['Compétences']);
        self::assertSame(route('portfolio.home', 'fr').'#projets', $names['Projets']);
        self::assertSame(route('portfolio.home', 'fr').'#contact', $names['Contact']);
    }

    public function test_projects_index_has_a_breadcrumb_and_the_privacy_page_is_in_the_sitemap(): void
    {
        $crumbs = $this->graph('/en/projects')['BreadcrumbList'][0]['itemListElement'];
        self::assertSame(['Home', 'Projects'], array_column($crumbs, 'name'));

        $this->get('/sitemap.xml')->assertOk()->assertSee('/fr/privacy', false)->assertSee('/en/privacy', false);
    }

    public function test_og_site_name_is_present(): void
    {
        $this->get('/fr')->assertSee('property="og:site_name"', false);
    }
}
