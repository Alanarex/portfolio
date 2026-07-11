<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class FrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_is_server_rendered(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Développeur full-stack PHP / Laravel')
            ->assertSee('class="skip-link"', false)
            ->assertSee('class="site-header"', false)
            ->assertSee('class="site-footer"', false)
            ->assertSee('data-three-preview', false);
    }

    public function test_guest_can_view_login_but_not_dashboard(): void
    {
        $this->get('/login')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_administrator_can_login_and_logout(): void
    {
        $admin = User::factory()->create(['password' => 'secret-pass', 'is_administrator' => true]);

        $this->post('/login', ['email' => $admin->email, 'password' => 'secret-pass'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_non_administrator_cannot_start_admin_session(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass', 'is_administrator' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_administrator_sees_minimal_dashboard_props(): void
    {
        $admin = User::factory()->create(['is_administrator' => true]);
        $this->actingAs($admin)->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('auth.user.email', $admin->email)
            ->has('projects', 0));
    }

    public function test_dashboard_design_system_landmarks_render_for_an_administrator(): void
    {
        $admin = User::factory()->create(['is_administrator' => true]);

        $this->actingAs($admin)
            ->withSession(['auth_version' => $admin->auth_version])
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
    }
}
