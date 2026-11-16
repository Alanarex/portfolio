<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Core\Security\IpMask;
use App\Core\Security\LoginJournal;
use App\Core\Security\UserAgentSummary;
use App\Models\AdminLoginEvent;
use App\Models\User;
use App\Support\AdministratorPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class AdminAccountTest extends TestCase
{
    use RefreshDatabase;

    // Mots de passe factices générés à l'exécution (aucun secret en clair dans le dépôt).
    private static string $current;

    private static string $strong;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$current = Str::random(10).'-aA1!';
        self::$strong = Str::random(10).'#Zz9?';
    }

    private function admin(): User
    {
        return User::factory()->administrator()->create([
            'email' => 'admin@example.test',
            'password' => self::$current,
        ]);
    }

    private function as(User $user): static
    {
        return $this->actingAs($user)->withSession(['auth_version' => $user->auth_version]);
    }

    public function test_the_account_panel_requires_an_administrator(): void
    {
        $this->get('/dashboard/account')->assertRedirect('/login');
        $this->put('/dashboard/account/password', [])->assertRedirect('/login');
        $this->delete('/dashboard/account/sessions')->assertRedirect('/login');

        $user = User::factory()->create();
        $this->as($user)->get('/dashboard/account')->assertForbidden();
        $this->as($user)->put('/dashboard/account/profile', ['name' => 'X', 'email' => 'x@example.test'])->assertForbidden();
        $this->as($user)->delete('/dashboard/account/sessions')->assertForbidden();
    }

    public function test_every_mutation_is_a_non_get_route_in_the_csrf_protected_web_group(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($route): bool => str_starts_with($route->uri(), 'dashboard/account'));

        $this->assertCount(5, $routes);
        foreach ($routes as $route) {
            $this->assertContains('web', $route->gatherMiddleware());
            $this->assertContains('admin', $route->gatherMiddleware());
            if ($route->uri() !== 'dashboard/account') {
                $this->assertSame([], array_intersect(['GET', 'HEAD'], $route->methods()), $route->uri().' must not be readable by GET');
            }
        }
    }

    public function test_the_panel_never_exposes_password_hash_tokens_or_session_ids(): void
    {
        config(['session.driver' => 'database']);
        $admin = $this->admin();
        DB::table('sessions')->insert([
            'id' => 'raw-session-id-should-not-leak', 'user_id' => $admin->id, 'ip_address' => '203.0.113.42',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'payload' => '', 'last_activity' => time() - 60,
        ]);

        $response = $this->as($admin)->get('/dashboard/account')->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Account/Show')
            ->where('account.email', 'admin@example.test')
            ->where('sessions.supported', true)
            ->where('sessions.items.0.ip', '203.0.•.•')
            ->where('sessions.items.0.platform', 'iOS')
            ->where('sessions.items.0.browser', 'Safari')
            ->where('sessions.items.0.device', 'mobile')
            ->missing('account.password'));

        $body = (string) $response->getContent();
        $this->assertStringNotContainsString($admin->password, $body);
        $this->assertStringNotContainsString('raw-session-id-should-not-leak', $body);
        $this->assertStringNotContainsString('203.0.113.42', $body);
        $this->assertStringNotContainsString((string) $admin->remember_token, $body);
    }

    public function test_profile_update_changes_the_name_without_password_when_the_email_is_unchanged(): void
    {
        $admin = $this->admin();

        $this->as($admin)->put('/dashboard/account/profile', ['name' => 'Alaa K.', 'email' => 'ADMIN@example.test'])
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('Alaa K.', $admin->fresh()->name);
    }

    public function test_changing_the_email_requires_the_current_password(): void
    {
        $admin = $this->admin();

        $this->as($admin)->put('/dashboard/account/profile', ['name' => 'A', 'email' => 'new@example.test'])
            ->assertSessionHasErrors('current_password');
        $this->as($admin)->put('/dashboard/account/profile', ['name' => 'A', 'email' => 'new@example.test', 'current_password' => 'wrong-password'])
            ->assertSessionHasErrors('current_password');
        $this->assertSame('admin@example.test', $admin->fresh()->email);

        $this->as($admin)->put('/dashboard/account/profile', ['name' => 'A', 'email' => ' New@Example.test ', 'current_password' => self::$current])
            ->assertSessionHasNoErrors();
        $this->assertSame('new@example.test', $admin->fresh()->email);
    }

    public function test_profile_validation_and_email_uniqueness(): void
    {
        $admin = $this->admin();
        User::factory()->create(['email' => 'taken@example.test']);

        $this->as($admin)->put('/dashboard/account/profile', ['name' => '', 'email' => 'not-an-email'])
            ->assertSessionHasErrors(['name', 'email']);
        $this->as($admin)->put('/dashboard/account/profile', ['name' => 'A', 'email' => 'taken@example.test', 'current_password' => self::$current])
            ->assertSessionHasErrors('email');
    }

    public function test_password_change_requires_current_password_and_the_provisioning_policy(): void
    {
        $admin = $this->admin();
        $hash = $admin->password;

        foreach (['short1A!', strtolower(Str::random(14)).'-9!', Str::password(20, true, false, true), Str::password(20, true, true, false)] as $weak) {
            $this->as($admin)->put('/dashboard/account/password', [
                'current_password' => self::$current, 'password' => $weak, 'password_confirmation' => $weak,
            ])->assertSessionHasErrors('password');
        }
        $this->as($admin)->put('/dashboard/account/password', [
            'current_password' => self::$current, 'password' => self::$strong, 'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');
        $this->as($admin)->put('/dashboard/account/password', [
            'current_password' => self::$current, 'password' => self::$current, 'password_confirmation' => self::$current,
        ])->assertSessionHasErrors('password');
        $this->as($admin)->put('/dashboard/account/password', [
            'current_password' => 'wrong-password', 'password' => self::$strong, 'password_confirmation' => self::$strong,
        ])->assertSessionHasErrors('current_password');

        $this->assertSame($hash, $admin->fresh()->password);
    }

    public function test_the_policy_is_the_one_used_by_the_provisioning_command(): void
    {
        $this->assertSame(12, AdministratorPassword::MIN_LENGTH);
        $this->assertTrue(validator(['p' => self::$strong], ['p' => [AdministratorPassword::rule()]])->passes());
        $this->assertFalse(validator(['p' => 'Short1!a'], ['p' => [AdministratorPassword::rule()]])->passes());
    }

    public function test_password_change_succeeds_bumps_the_auth_version_and_keeps_the_current_session(): void
    {
        $admin = $this->admin();

        $this->as($admin)->put('/dashboard/account/password', [
            'current_password' => self::$current, 'password' => self::$strong, 'password_confirmation' => self::$strong,
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $fresh = $admin->fresh();
        $this->assertTrue(Hash::check(self::$strong, $fresh->password));
        $this->assertSame(2, $fresh->auth_version);
        $this->assertSame(2, session('auth_version'));

        // Une ancienne session (auth_version = 1) est refusée.
        $this->flushSession();
        $this->actingAs($fresh)->withSession(['auth_version' => 1])->get('/dashboard/account')->assertRedirect('/login');
    }

    public function test_password_change_deletes_the_other_database_sessions(): void
    {
        config(['session.driver' => 'database']);
        $admin = $this->admin();
        DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $admin->id, 'payload' => '', 'last_activity' => time()]);
        DB::table('sessions')->insert(['id' => 'someone-else', 'user_id' => null, 'payload' => '', 'last_activity' => time()]);

        $this->as($admin)->put('/dashboard/account/password', [
            'current_password' => self::$current, 'password' => self::$strong, 'password_confirmation' => self::$strong,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['id' => 'other-device']);
        $this->assertDatabaseHas('sessions', ['id' => 'someone-else']);
    }

    public function test_wrong_current_password_attempts_are_rate_limited(): void
    {
        $admin = $this->admin();
        RateLimiter::clear('admin-account:password:'.$admin->id);

        for ($i = 0; $i < 5; $i++) {
            $this->as($admin)->put('/dashboard/account/password', [
                'current_password' => 'wrong-'.$i, 'password' => self::$strong, 'password_confirmation' => self::$strong,
            ])->assertSessionHasErrors('current_password');
        }

        // Même le bon mot de passe est refusé tant que la limite est atteinte, avec un message qui ne dit pas pourquoi.
        $this->as($admin)->put('/dashboard/account/password', [
            'current_password' => self::$current, 'password' => self::$strong, 'password_confirmation' => self::$strong,
        ])->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check(self::$current, $admin->fresh()->password));
    }

    public function test_an_administrator_can_sign_out_one_other_session_but_never_the_current_one(): void
    {
        config(['session.driver' => 'database']);
        $admin = $this->admin();
        DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $admin->id, 'payload' => '', 'last_activity' => time()]);

        $this->as($admin);
        $handle = null;
        $this->get('/dashboard/account')->assertInertia(function (Assert $page) use (&$handle): void {
            $items = $page->toArray()['props']['sessions']['items'];
            $handle = $items[0]['handle'];
        });
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', (string) $handle);

        $this->delete('/dashboard/account/sessions/'.$handle)->assertSessionHas('success');
        $this->assertDatabaseMissing('sessions', ['id' => 'other-device']);

        // Un identifiant inconnu ou mal formé ne supprime rien.
        $this->delete('/dashboard/account/sessions/'.str_repeat('0', 32))->assertSessionHas('success');
        $this->delete('/dashboard/account/sessions/not-a-handle')->assertNotFound();
    }

    public function test_sign_out_others_keeps_the_current_session_and_invalidates_the_rest(): void
    {
        config(['session.driver' => 'database']);
        $admin = $this->admin();
        DB::table('sessions')->insert(['id' => 'other-1', 'user_id' => $admin->id, 'payload' => '', 'last_activity' => time()]);
        DB::table('sessions')->insert(['id' => 'other-2', 'user_id' => $admin->id, 'payload' => '', 'last_activity' => time()]);

        $this->as($admin)->delete('/dashboard/account/sessions')->assertSessionHas('success');

        $this->assertSame(0, DB::table('sessions')->whereIn('id', ['other-1', 'other-2'])->count());
        $this->assertSame(2, $admin->fresh()->auth_version);
        $this->assertSame(2, session('auth_version'));
    }

    public function test_without_the_database_driver_the_list_is_unavailable_but_other_devices_can_still_be_signed_out(): void
    {
        $admin = $this->admin();

        $this->as($admin)->get('/dashboard/account')->assertInertia(fn (Assert $page) => $page
            ->where('sessions.supported', false)->where('sessions.items', []));

        $this->as($admin)->delete('/dashboard/account/sessions')->assertSessionHas('success');
        $this->assertSame(2, $admin->fresh()->auth_version);
    }

    public function test_login_attempts_are_journaled_with_a_masked_ip_and_no_secret(): void
    {
        $admin = $this->admin();

        $this->post('/login', ['email' => 'admin@example.test', 'password' => 'wrong'], ['REMOTE_ADDR' => '198.51.100.7'])
            ->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'admin@example.test', 'password' => self::$current], ['REMOTE_ADDR' => '198.51.100.7']);

        $events = AdminLoginEvent::query()->orderBy('id')->get();
        $this->assertCount(2, $events);
        $this->assertFalse($events[0]->succeeded);
        $this->assertTrue($events[1]->succeeded);
        $this->assertSame($admin->id, $events[1]->user_id);
        $this->assertSame('198.51.•.•', $events[1]->ip_masked);
        $this->assertStringNotContainsString('wrong', json_encode($events->toArray(), JSON_THROW_ON_ERROR));
    }

    public function test_the_journal_keeps_only_the_latest_events(): void
    {
        $admin = $this->admin();
        for ($i = 0; $i < AdminLoginEvent::KEEP + 5; $i++) {
            LoginJournal::record(request(), false, null);
        }

        $this->assertSame(AdminLoginEvent::KEEP, AdminLoginEvent::query()->count());
        $this->as($admin)->get('/dashboard/account')->assertInertia(fn (Assert $page) => $page->has('logins', 10));
    }

    public function test_messages_follow_the_visitor_language(): void
    {
        $lower = strtolower(Str::random(14)).'-9!';
        $admin = $this->admin();

        $this->as($admin)->withHeaders(['Accept-Language' => 'en'])
            ->put('/dashboard/account/password', ['current_password' => 'wrong-password', 'password' => self::$strong, 'password_confirmation' => self::$strong])
            ->assertSessionHasErrors(['current_password' => 'The current password is incorrect.']);

        $this->as($admin)->withHeaders(['Accept-Language' => 'fr-FR'])
            ->put('/dashboard/account/password', ['current_password' => 'wrong-password', 'password' => self::$strong, 'password_confirmation' => self::$strong])
            ->assertSessionHasErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);

        $this->as($admin)->withHeaders(['Accept-Language' => 'fr'])
            ->put('/dashboard/account/password', ['current_password' => self::$current, 'password' => $lower, 'password_confirmation' => $lower])
            ->assertSessionHasErrors(['password' => 'Le mot de passe doit contenir au moins une majuscule et une minuscule.']);
    }

    public function test_the_single_administrator_design_has_no_multi_account_routes(): void
    {
        $this->assertFalse(collect(app('router')->getRoutes()->getRoutes())->contains(
            fn ($route): bool => str_starts_with($route->uri(), 'dashboard/administrators'),
        ));
    }

    public function test_ip_and_user_agent_helpers(): void
    {
        $this->assertSame('192.168.•.•', IpMask::mask('192.168.1.20'));
        $this->assertSame('2001:0db8:•:•', IpMask::mask('2001:db8:85a3::8a2e:370:7334'));
        $this->assertNull(IpMask::mask('not an ip'));
        $this->assertNull(IpMask::mask(null));

        $chrome = UserAgentSummary::parse('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36');
        $this->assertSame(['browser' => 'Chrome', 'platform' => 'Linux', 'device' => 'desktop'], $chrome);
        $this->assertSame('unknown', UserAgentSummary::parse(null)['device']);
    }
}
