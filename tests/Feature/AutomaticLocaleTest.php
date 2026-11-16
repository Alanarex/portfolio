<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\PreferredLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class AutomaticLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /** @return array<string, array{string, string}> */
    public static function acceptLanguageProvider(): array
    {
        return [
            'français' => ['fr-FR,fr;q=0.9,en;q=0.5', '/fr'],
            'anglais' => ['en-GB,en;q=0.9', '/en'],
            'allemand : repli anglais' => ['de-DE,de;q=0.9', '/en'],
            'qualités : anglais devant français' => ['en;q=0.9,fr;q=0.8', '/en'],
            'qualités : français devant anglais' => ['en;q=0.4,fr;q=0.8', '/fr'],
            'langue non gérée puis français' => ['de,fr;q=0.7', '/fr'],
            'q=0 exclu' => ['fr;q=0,en;q=0.1', '/en'],
            'joker seul' => ['*', '/fr'],
            'en-tête vide' => ['', '/fr'],
        ];
    }

    #[DataProvider('acceptLanguageProvider')]
    public function test_root_follows_accept_language(string $header, string $expected): void
    {
        $this->withHeader('Accept-Language', $header)->get('/')->assertStatus(302)->assertRedirect($expected);
    }

    public function test_root_without_header_falls_back_to_french_like_x_default(): void
    {
        // Le client de test envoie « en-us » par défaut : on retire l'en-tête pour de bon.
        $this->withHeader('Accept-Language', '')->get('/')->assertStatus(302)->assertRedirect('/fr');
    }

    public function test_the_redirect_is_temporary_varies_and_is_never_cached(): void
    {
        $response = $this->withHeader('Accept-Language', 'en')->get('/')->assertStatus(302);

        $vary = implode(', ', $response->headers->all('Vary'));
        self::assertStringContainsString('Accept-Language', $vary);
        self::assertStringContainsString('Cookie', $vary);
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_the_saved_choice_beats_accept_language(): void
    {
        $this->withUnencryptedCookie(PreferredLocale::COOKIE, 'fr')->withHeader('Accept-Language', 'en')->get('/')->assertRedirect('/fr');
        $this->withUnencryptedCookie(PreferredLocale::COOKIE, 'en')->withHeader('Accept-Language', 'fr')->get('/')->assertRedirect('/en');
    }

    public function test_an_invalid_cookie_is_ignored(): void
    {
        $this->withUnencryptedCookie(PreferredLocale::COOKIE, 'de')->withHeader('Accept-Language', 'en')->get('/')->assertRedirect('/en');
    }

    public function test_prefixed_urls_are_never_redirected(): void
    {
        $this->withUnencryptedCookie(PreferredLocale::COOKIE, 'fr')->withHeader('Accept-Language', 'fr')->get('/en')->assertOk()->assertSee('<html lang="en"', false);
        $this->withUnencryptedCookie(PreferredLocale::COOKIE, 'en')->withHeader('Accept-Language', 'en')->get('/fr')->assertOk()->assertSee('<html lang="fr"', false);
        $this->withHeader('Accept-Language', 'de')->get('/fr/legal')->assertOk();
        $this->withHeader('Accept-Language', 'de')->get('/en/privacy')->assertOk();
    }

    public function test_unprefixed_pages_are_redirected_to_the_resolved_language(): void
    {
        $this->withHeader('Accept-Language', 'en')->get('/projects')->assertStatus(302)->assertRedirect('/en/projects');
        $this->withHeader('Accept-Language', 'fr')->get('/privacy')->assertStatus(302)->assertRedirect('/fr/privacy');
        $this->withHeader('Accept-Language', 'en')->get('/projects/mon-projet')->assertStatus(302)->assertRedirect('/en/projects/mon-projet');
        $this->withHeader('Accept-Language', 'en')->get('/?utm=x')->assertRedirect('/en?utm=x');
    }

    public function test_crawlers_reach_both_languages_directly_and_get_french_on_the_root(): void
    {
        $bots = [
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
            'DuckDuckBot/1.1',
        ];

        foreach ($bots as $bot) {
            $this->withHeader('User-Agent', $bot)->get('/fr')->assertOk();
            $this->withHeader('User-Agent', $bot)->get('/en')->assertOk();
            // Même avec Accept-Language: en, la racine reste cohérente avec x-default (/fr).
            $this->withHeaders(['User-Agent' => $bot, 'Accept-Language' => 'en'])->get('/')->assertStatus(302)->assertRedirect('/fr');
        }
    }

    public function test_seo_markup_keeps_hreflang_canonical_and_sitemap(): void
    {
        $page = $this->get('/en')->assertOk();
        $page->assertSee('hreflang="fr" href="'.url('/fr').'"', false);
        $page->assertSee('hreflang="en" href="'.url('/en').'"', false);
        $page->assertSee('hreflang="x-default" href="'.url('/fr').'"', false);
        $page->assertSee('rel="canonical" href="'.url('/en').'"', false);

        $sitemap = $this->get('/sitemap.xml')->assertOk();
        $sitemap->assertSee(url('/fr'), false)->assertSee(url('/en'), false);
    }

    public function test_error_pages_follow_cookie_then_header(): void
    {
        $this->withHeader('Accept-Language', 'de')->get('/page-introuvable')->assertStatus(404)->assertSee('Page not found', false);
        $this->withUnencryptedCookie(PreferredLocale::COOKIE, 'fr')->withHeader('Accept-Language', 'en')->get('/page-introuvable')->assertStatus(404)->assertSee('Page introuvable', false);
    }

    public function test_the_login_screen_follows_the_resolved_language(): void
    {
        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/login')->assertOk()->assertSee('&quot;locale&quot;:&quot;en&quot;', false);
        $this->withHeader('Accept-Language', 'fr-FR')->get('/login')->assertOk()->assertSee('&quot;locale&quot;:&quot;fr&quot;', false);
        $this->withUnencryptedCookie(PreferredLocale::COOKIE, 'fr')->withHeader('Accept-Language', 'en')->get('/login')->assertSee('&quot;locale&quot;:&quot;fr&quot;', false);
    }

    public function test_the_contact_form_uses_the_url_language(): void
    {
        $this->withHeader('Accept-Language', 'fr')->post('/en/contact', [])->assertSessionHasErrors();
        self::assertSame('en', app()->getLocale());
    }
}
