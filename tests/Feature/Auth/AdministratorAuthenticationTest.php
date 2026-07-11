<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class AdministratorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_an_administrator_can_authenticate_and_email_is_normalized(): void
    {
        $administrator = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'Correct-password-1!',
            'is_administrator' => true,
        ]);

        $this->post('/login', [
            'email' => ' ADMIN@EXAMPLE.TEST ',
            'password' => 'Correct-password-1!',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($administrator);
    }

    public function test_successful_login_regenerates_the_session_and_never_issues_a_remember_cookie(): void
    {
        $administrator = User::factory()->create([
            'password' => 'Correct-password-1!',
            'is_administrator' => true,
        ]);
        $this->withSession(['before_login' => true]);
        $sessionId = session()->getId();

        $response = $this->post('/login', [
            'email' => $administrator->email,
            'password' => 'Correct-password-1!',
            'remember' => true,
        ]);

        self::assertNotSame($sessionId, session()->getId());
        $response->assertCookieMissing(Auth::guard()->getRecallerName());
    }

    public function test_logout_invalidates_the_session(): void
    {
        $administrator = User::factory()->create([
            'password' => 'Correct-password-1!',
            'is_administrator' => true,
        ]);
        $this->post('/login', [
            'email' => $administrator->email,
            'password' => 'Correct-password-1!',
            'remember' => true,
        ]);
        $this->withSession(['private' => 'value']);
        $sessionId = session()->getId();

        $this->post('/logout')->assertRedirect('/');

        $this->assertGuest();
        self::assertNotSame($sessionId, session()->getId());
        self::assertNull(session('private'));
    }

    public function test_non_administrator_never_gets_an_authenticated_session(): void
    {
        $user = User::factory()->create([
            'password' => 'Correct-password-1!',
            'is_administrator' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Correct-password-1!',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_limited_by_normalized_email_and_ip(): void
    {
        Cache::flush();
        User::factory()->create(['email' => 'admin@example.test', 'is_administrator' => true]);

        foreach (['ADMIN@example.test', ' admin@example.test ', 'Admin@Example.Test', 'ADMIN@EXAMPLE.TEST', 'admin@example.test'] as $email) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
                ->post('/login', ['email' => $email, 'password' => 'wrong-password'])
                ->assertSessionHasErrors('email');
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
            ->post('/login', ['email' => 'admin@example.test', 'password' => 'wrong-password'])
            ->assertTooManyRequests();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post('/login', ['email' => 'admin@example.test', 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
    }

    public function test_authentication_events_are_audited_without_credentials(): void
    {
        Log::spy();
        $administrator = User::factory()->create([
            'password' => 'Correct-password-1!',
            'is_administrator' => true,
        ]);

        $this->post('/login', ['email' => $administrator->email, 'password' => 'wrong']);
        $this->post('/login', ['email' => $administrator->email, 'password' => 'Correct-password-1!']);
        $this->post('/logout');

        Log::shouldHaveReceived('info')->withArgs(fn (string $message, array $context): bool => $message === 'security.audit'
            && $context['event'] === 'admin.login.failed'
            && is_string($context['request_id'])
            && $context['request_id'] !== '');
        Log::shouldHaveReceived('info')->withArgs(fn (string $message, array $context): bool => $message === 'security.audit'
            && $context['event'] === 'admin.login.succeeded'
            && is_string($context['request_id'])
            && $context['request_id'] !== ''
            && $context['user_id'] === $administrator->getKey());
        Log::shouldHaveReceived('info')->withArgs(fn (string $message, array $context): bool => $message === 'security.audit'
            && $context['event'] === 'admin.logout'
            && is_string($context['request_id'])
            && $context['request_id'] !== ''
            && $context['user_id'] === $administrator->getKey());
    }

    public function test_administrator_attribute_is_not_mass_assignable(): void
    {
        $user = new User;
        $user->fill(['name' => 'User', 'email' => 'user@example.test', 'password' => 'password', 'is_administrator' => true]);

        self::assertFalse($user->isAdministrator());
    }
}
