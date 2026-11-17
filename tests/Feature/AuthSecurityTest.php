<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminLoginEvent;
use App\Models\User;
use App\Notifications\AdminPasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

final class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    private static string $secret;

    private static string $fresh;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$secret = Str::random(10).'-aA1!';
        self::$fresh = Str::random(10).'#Zz9?';
    }

    private function admin(): User
    {
        return User::factory()->administrator()->create(['email' => 'admin@example.test', 'password' => self::$secret]);
    }

    private function attempt(string $email, string $password): TestResponse
    {
        return $this->from('/login')->post('/login', ['email' => $email, 'password' => $password]);
    }

    public function test_login_is_locked_after_three_failures_even_with_the_right_password(): void
    {
        $this->admin();
        for ($i = 0; $i < 2; $i++) {
            $this->attempt('admin@example.test', 'wrong-'.$i)->assertSessionHasErrors('email');
        }
        // La 3e erreur déclenche le verrouillage et le signale.
        $this->attempt('admin@example.test', 'wrong-3')->assertSessionHasErrors(['email' => __('auth.throttle', ['minutes' => 1])]);

        // Bon mot de passe pendant le verrouillage : refusé, toujours sans session ouverte.
        $this->attempt('admin@example.test', self::$secret)->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->assertSame(2, AdminLoginEvent::query()->where('reason', 'locked')->count());
        $this->assertSame(1, AdminLoginEvent::query()->where('reason', 'failed')->count() - 1);
    }

    public function test_lockout_expires_and_grows(): void
    {
        $this->admin();
        for ($i = 0; $i < 3; $i++) {
            $this->attempt('admin@example.test', 'wrong');
        }
        $this->travel(61)->seconds();
        $this->attempt('admin@example.test', self::$secret)->assertRedirect('/dashboard');
        $this->post('/logout');

        // Deuxième verrouillage : 5 min de délai, il reste ~4 min après 61 s.
        $this->travel(1)->second();
        $other = 'ghost@example.test';
        for ($i = 0; $i < 3; $i++) {
            $this->attempt($other, 'wrong');
        }
        $this->travel(61)->seconds();
        for ($i = 0; $i < 3; $i++) {
            $this->attempt($other, 'wrong');
        }
        $this->travel(61)->seconds();
        $this->attempt($other, 'wrong')->assertSessionHasErrors(['email' => __('auth.throttle', ['minutes' => 4])]);
    }

    public function test_failure_message_is_identical_for_unknown_and_known_accounts(): void
    {
        $this->admin();
        $this->attempt('admin@example.test', 'nope')->assertSessionHasErrors(['email' => __('auth.failed')]);
        $this->attempt('nobody@example.test', 'nope')->assertSessionHasErrors(['email' => __('auth.failed')]);

        // Même séquence de verrouillage pour un compte inexistant : rien ne permet de le distinguer.
        foreach ([1, 2] as $_) {
            $this->attempt('nobody@example.test', 'nope');
        }
        $this->attempt('nobody@example.test', 'nope')->assertSessionHasErrors(['email' => __('auth.throttle', ['minutes' => 1])]);
    }

    public function test_the_journal_never_stores_the_typed_email(): void
    {
        $this->attempt('nobody@example.test', 'nope');
        $this->assertStringNotContainsString('nobody', json_encode(AdminLoginEvent::query()->get()->toArray(), JSON_THROW_ON_ERROR));
    }

    public function test_forgot_password_answers_the_same_for_known_and_unknown_addresses(): void
    {
        Notification::fake();
        $this->admin();

        $a = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'admin@example.test']);
        $b = $this->from('/forgot-password')->post('/forgot-password', ['email' => 'nobody@example.test']);

        $a->assertRedirect('/forgot-password')->assertSessionHas('success', __('auth.forgot.sent'));
        $b->assertRedirect('/forgot-password')->assertSessionHas('success', __('auth.forgot.sent'));
        $this->assertSame($a->getStatusCode(), $b->getStatusCode());
        Notification::assertSentToTimes(User::query()->first(), AdminPasswordResetNotification::class, 1);
        Notification::assertCount(1);
    }

    public function test_non_administrators_never_receive_a_reset_link(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'plain@example.test']);
        $this->post('/forgot-password', ['email' => 'plain@example.test'])->assertSessionHas('success');
        Notification::assertNothingSent();
    }

    public function test_forgot_password_is_rate_limited(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post('/forgot-password', ['email' => "x{$i}@example.test"])->assertRedirect();
        }
        $this->post('/forgot-password', ['email' => 'x9@example.test'])->assertStatus(429);
    }

    /** @return array{0: User, 1: string} */
    private function issueToken(): array
    {
        $user = $this->admin();
        $token = Password::broker()->createToken($user);

        return [$user, $token];
    }

    public function test_reset_flow_changes_the_password_once_and_revokes_sessions(): void
    {
        config(['session.driver' => 'database']);
        [$user, $token] = $this->issueToken();
        $version = $user->auth_version;
        DB::table('sessions')->insert(['id' => 'abc', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'x', 'payload' => '', 'last_activity' => time()]);

        // Le jeton est stocké haché.
        $this->assertNotSame($token, DB::table('password_reset_tokens')->value('token'));

        $url = URL::temporarySignedRoute('password.reset', now()->addMinutes(30), ['token' => $token, 'email' => $user->email]);
        $this->get($url)->assertOk();

        $payload = ['token' => $token, 'email' => $user->email, 'password' => self::$fresh, 'password_confirmation' => self::$fresh];
        $this->post('/reset-password', $payload)->assertRedirect('/login')->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check(self::$fresh, $user->password));
        $this->assertSame($version + 1, $user->auth_version);
        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());
        $this->assertSame(1, AdminLoginEvent::query()->where('reason', 'reset_completed')->count());

        // Usage unique : le même jeton est refusé ensuite.
        $this->post('/reset-password', [...$payload, 'password' => self::$fresh.'x', 'password_confirmation' => self::$fresh.'x'])
            ->assertSessionHasErrors(['email' => __('auth.forgot.invalid')]);
    }

    public function test_reset_rejects_unsigned_tampered_and_expired_links_and_weak_passwords(): void
    {
        [$user, $token] = $this->issueToken();

        $this->get('/reset-password/'.$token.'?email='.$user->email)->assertForbidden();

        $signed = URL::temporarySignedRoute('password.reset', now()->addMinutes(30), ['token' => $token, 'email' => $user->email]);
        $this->get($signed.'&email=other%40example.test')->assertForbidden();

        $this->travel(31)->minutes();
        $this->get($signed)->assertForbidden();
        $this->travel(-31)->minutes();

        Cache::flush(); // le limiteur de réinitialisation a déjà compté les GET ci-dessus
        $weak = ['token' => $token, 'email' => $user->email, 'password' => 'short1A!', 'password_confirmation' => 'short1A!'];
        $this->post('/reset-password', $weak)->assertSessionHasErrors('password');
        $this->assertFalse(Hash::check('short1A!', $user->fresh()->password));

        $bad = ['token' => Str::random(64), 'email' => $user->email, 'password' => self::$fresh, 'password_confirmation' => self::$fresh];
        $this->post('/reset-password', $bad)->assertSessionHasErrors(['email' => __('auth.forgot.invalid')]);
        $unknown = [...$bad, 'email' => 'nobody@example.test'];
        $this->post('/reset-password', $unknown)->assertSessionHasErrors(['email' => __('auth.forgot.invalid')]);
    }

    public function test_reset_token_expires(): void
    {
        [$user, $token] = $this->issueToken();
        $this->travel(31)->minutes();
        $this->post('/reset-password', ['token' => $token, 'email' => $user->email, 'password' => self::$fresh, 'password_confirmation' => self::$fresh])
            ->assertSessionHasErrors(['email' => __('auth.forgot.invalid')]);
    }

    public function test_reset_email_is_localized_and_signed(): void
    {
        $user = $this->admin();
        foreach (['fr', 'en'] as $locale) {
            $mail = (new AdminPasswordResetNotification('t'.Str::random(40), $locale))->toMail($user);
            $this->assertSame(__('auth.mail.subject', [], $locale), $mail->subject);
            $this->assertStringContainsString('signature=', $mail->actionUrl);
            $this->assertStringContainsString('expires=', $mail->actionUrl);
        }
    }

    public function test_security_headers_are_present_with_a_nonce_based_csp(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $csp = (string) $response->headers->get('Content-Security-Policy');

        foreach (["default-src 'self'", "object-src 'none'", "frame-ancestors 'none'", "base-uri 'self'", "form-action 'self'", "'wasm-unsafe-eval'"] as $directive) {
            $this->assertStringContainsString($directive, $csp);
        }
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-[A-Za-z0-9+\\/=]{16,}'/", $csp);
        $this->assertStringNotContainsString("'unsafe-eval'", $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString('camera=()', (string) $response->headers->get('Permissions-Policy'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_inline_scripts_carry_the_request_nonce_and_hsts_is_https_only(): void
    {
        $response = $this->get('/login');
        preg_match("/'nonce-([^']+)'/", (string) $response->headers->get('Content-Security-Policy'), $m);
        $this->assertStringContainsString('nonce="'.$m[1].'"', $response->getContent());
        $this->assertNull($response->headers->get('Strict-Transport-Security'));

        $https = $this->get('https://localhost/login');
        $this->assertStringContainsString('max-age=31536000', (string) $https->headers->get('Strict-Transport-Security'));

        $public = $this->get('/fr');
        $this->assertStringContainsString("frame-ancestors 'none'", (string) $public->headers->get('Content-Security-Policy'));
    }

    public function test_the_csp_nonce_changes_on_every_request(): void
    {
        $a = (string) $this->get('/login')->headers->get('Content-Security-Policy');
        $b = (string) $this->get('/login')->headers->get('Content-Security-Policy');
        $this->assertNotSame($a, $b);
    }

    public function test_session_cookie_is_secure_httponly_and_samesite_by_default(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
    }

    public function test_session_id_is_regenerated_on_login(): void
    {
        $this->admin();
        $this->startSession();
        $before = session()->getId();
        $this->attempt('admin@example.test', self::$secret)->assertRedirect('/dashboard');
        $this->assertNotSame($before, session()->getId());
    }
}
