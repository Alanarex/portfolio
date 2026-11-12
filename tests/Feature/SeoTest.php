<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class SeoTest extends TestCase
{
    public function test_home_exposes_canonical_open_graph_and_structured_data(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('Développeur full-stack PHP / Laravel');
    }

    public function test_private_area_is_not_linked_from_public_pages(): void
    {
        $this->get('/')->assertDontSee('Espace privé')->assertDontSee('href="'.route('login').'"', false);
    }

    public function test_robots_and_sitemap_are_served(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /dashboard');
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('legal'), false);
    }

    public function test_error_pages_are_not_indexed(): void
    {
        $this->get('/page-inexistante')->assertNotFound()->assertSee('noindex', false);
    }
}
