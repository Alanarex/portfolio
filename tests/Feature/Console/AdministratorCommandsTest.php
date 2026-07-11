<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class AdministratorCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_provision_creates_and_rotates_the_only_administrator(): void
    {
        $this->artisan('admin:provision', ['email' => ' ADMIN@EXAMPLE.TEST '])
            ->expectsQuestion('Administrator password', 'First-secure-password-1!')
            ->expectsQuestion('Confirm administrator password', 'First-secure-password-1!')
            ->assertSuccessful();

        $administrator = User::query()->where('email', 'admin@example.test')->sole();
        $administrator->refresh();
        self::assertTrue($administrator->isAdministrator());
        self::assertTrue(Hash::check('First-secure-password-1!', $administrator->password));
        $previousAuthVersion = $administrator->auth_version;

        $this->artisan('admin:provision', ['email' => 'admin@example.test'])
            ->expectsQuestion('Administrator password', 'Rotated-secure-password-2!')
            ->expectsQuestion('Confirm administrator password', 'Rotated-secure-password-2!')
            ->assertSuccessful();

        $administrator->refresh();
        self::assertTrue(Hash::check('Rotated-secure-password-2!', $administrator->password));
        self::assertSame($previousAuthVersion + 1, $administrator->auth_version);
        self::assertNull($administrator->remember_token);
    }

    public function test_provision_refuses_a_second_administrator(): void
    {
        User::factory()->create(['email' => 'first@example.test', 'is_administrator' => true]);

        $this->artisan('admin:provision', ['email' => 'second@example.test'])
            ->expectsQuestion('Administrator password', 'Another-secure-password-1!')
            ->expectsQuestion('Confirm administrator password', 'Another-secure-password-1!')
            ->expectsOutput('An administrator already exists.')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'second@example.test']);
    }

    public function test_password_reset_is_interactive_and_revokes_sessions_and_remember_token(): void
    {
        Log::spy();
        $administrator = User::factory()->create([
            'password' => 'Old-password-1!',
            'remember_token' => 'remember-me',
            'is_administrator' => true,
        ]);
        $administrator->refresh();
        $previousAuthVersion = $administrator->auth_version;

        $this->artisan('admin:reset-password')
            ->expectsQuestion('New administrator password', 'New-secure-password-2!')
            ->expectsQuestion('Confirm administrator password', 'New-secure-password-2!')
            ->expectsOutput('Administrator password reset and active sessions revoked.')
            ->assertSuccessful();

        $administrator->refresh();
        self::assertTrue(Hash::check('New-secure-password-2!', $administrator->password));
        self::assertNull($administrator->remember_token);
        self::assertSame($previousAuthVersion + 1, $administrator->auth_version);
        Log::shouldHaveReceived('info')->with('security.audit', [
            'event' => 'admin.password_reset.succeeded', 'user_id' => $administrator->getKey(),
        ]);
    }

    public function test_password_reset_revokes_an_existing_session_independently_of_the_session_store(): void
    {
        $administrator = User::factory()->create(['is_administrator' => true]);
        $administrator->refresh();
        $oldVersion = $administrator->auth_version;

        $this->artisan('admin:reset-password')
            ->expectsQuestion('New administrator password', 'New-secure-password-2!')
            ->expectsQuestion('Confirm administrator password', 'New-secure-password-2!')
            ->assertSuccessful();

        $this->actingAs($administrator->fresh())
            ->withSession(['auth_version' => $oldVersion])
            ->get('/dashboard')
            ->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_password_reset_fails_without_an_administrator(): void
    {
        $this->artisan('admin:reset-password')
            ->expectsOutput('No administrator exists.')
            ->assertFailed();
    }

    public function test_password_reset_rejects_a_weak_password(): void
    {
        User::factory()->create(['is_administrator' => true]);

        $this->artisan('admin:reset-password')
            ->expectsQuestion('New administrator password', 'weak')
            ->expectsQuestion('Confirm administrator password', 'weak')
            ->assertFailed();
    }
}
