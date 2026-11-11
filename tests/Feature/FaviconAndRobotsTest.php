<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FaviconAndRobotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_expose_light_and_dark_favicons(): void
    {
        $this->get('/fr')
            ->assertOk()
            ->assertSee('favicons/light/favicon-32x32.png', false)
            ->assertSee('media="(prefers-color-scheme: light)"', false)
            ->assertSee('favicons/dark/favicon-32x32.png', false)
            ->assertSee('media="(prefers-color-scheme: dark)"', false)
            ->assertSee('rel="apple-touch-icon"', false)
            ->assertSee('rel="manifest"', false);
    }

    public function test_every_referenced_icon_file_exists_and_is_not_empty(): void
    {
        $files = [
            'favicon.ico',
            'apple-touch-icon.png',
            'site.webmanifest',
            'favicons/android-chrome-192x192.png',
            'favicons/android-chrome-512x512.png',
            'favicons/light/favicon-16x16.png',
            'favicons/light/favicon-32x32.png',
            'favicons/dark/favicon-16x16.png',
            'favicons/dark/favicon-32x32.png',
        ];

        foreach ($files as $file) {
            self::assertFileExists(public_path($file));
            self::assertGreaterThan(0, filesize(public_path($file)), $file.' is empty');
        }

        $manifest = json_decode((string) file_get_contents(public_path('site.webmanifest')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($manifest['icons'] as $icon) {
            self::assertFileExists(public_path(ltrim((string) $icon['src'], '/')));
        }
    }

    public function test_robots_is_served_by_the_application_not_a_static_file(): void
    {
        // A static public/robots.txt would shadow the route and hide the Sitemap line.
        self::assertFileDoesNotExist(public_path('robots.txt'));

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: '.route('portfolio.sitemap'), false)
            ->assertSee('Disallow: /dashboard', false);
    }
}
