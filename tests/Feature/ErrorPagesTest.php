<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class ErrorPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        foreach (['fr', 'en'] as $locale) {
            Route::get("/{$locale}/_error/{code}", fn (int $code) => abort($code))->whereNumber('code');
        }
        Route::get('/_error/{code}', fn (int $code) => abort($code))->whereNumber('code');
    }

    public function test_every_http_error_uses_the_public_design_in_french(): void
    {
        $titles = [
            401 => 'Authentification requise', 403 => 'Accès refusé', 404 => 'Page introuvable', 419 => 'Session expirée',
            429 => 'Trop de requêtes', 500 => 'Une erreur est survenue', 503 => 'Maintenance en cours',
        ];

        foreach ($titles as $code => $title) {
            $response = $this->get("/fr/_error/{$code}")->assertStatus($code);
            $response->assertSee($title, false);
            $response->assertSee('class="public-portfolio"', false);
            $response->assertSee('class="container error-page"', false);
            $response->assertSee('lang="fr"', false);
            $response->assertSee('Retour au portfolio', false);
            $response->assertSee('noindex', false);
        }
    }

    public function test_the_language_follows_the_url_and_defaults_to_the_browser(): void
    {
        $this->get('/en/_error/404')->assertStatus(404)->assertSee('Page not found', false)->assertSee('lang="en"', false);
        $this->get('/en/inconnu')->assertStatus(404)->assertSee('Page not found', false);
        $this->get('/fr/inconnu')->assertStatus(404)->assertSee('Page introuvable', false);
        // Without a language in the URL the browser preference decides (test requests default to English).
        $this->withHeader('Accept-Language', 'fr-FR,fr;q=0.9')->get('/_error/403')->assertStatus(403)->assertSee('Accès refusé', false);
        $this->withHeader('Accept-Language', 'en-GB,en;q=0.9')->get('/_error/403')->assertStatus(403)->assertSee('Access denied', false);
    }

    public function test_error_pages_do_not_use_the_legacy_stylesheet_or_leak_details(): void
    {
        $content = $this->get('/fr/_error/500')->assertStatus(500)->getContent();

        self::assertStringNotContainsString('css/app.css', (string) $content);
        self::assertStringNotContainsString('Server Error', (string) $content);
        self::assertStringNotContainsString('Stack trace', (string) $content);
    }
}
