<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Security\AdminSessions;
use App\Core\Security\SecurityAudit;
use App\Core\Security\UserAgentSummary;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAccountPasswordRequest;
use App\Http\Requests\Admin\UpdateAccountProfileRequest;
use App\Models\AdminLoginEvent;
use App\Models\User;
use App\Support\AdministratorPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class AccountController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function show(Request $request): Response
    {
        Gate::authorize('manage-account');
        $user = $this->administrator($request);

        return Inertia::render('Admin/Account/Show', [
            'account' => ['name' => $user->name, 'email' => $user->email],
            'password_min_length' => AdministratorPassword::MIN_LENGTH,
            'sessions' => [
                'supported' => AdminSessions::supported(),
                'items' => AdminSessions::list($user, $request->session()),
            ],
            'logins' => AdminLoginEvent::query()->orderByDesc('id')->limit(10)->get()
                ->map(fn (AdminLoginEvent $event): array => [
                    'id' => $event->id,
                    'succeeded' => $event->succeeded,
                    'ip' => $event->ip_masked,
                    'at' => $event->created_at->toIso8601String(),
                    'browser' => UserAgentSummary::parse($event->user_agent)['browser'],
                    'platform' => UserAgentSummary::parse($event->user_agent)['platform'],
                ])->all(),
        ]);
    }

    public function updateProfile(UpdateAccountProfileRequest $request): RedirectResponse
    {
        $user = $this->administrator($request);
        $email = (string) $request->validated('email');
        $emailChanged = $email !== mb_strtolower($user->email);

        if ($emailChanged) {
            $this->confirmCurrentPassword($user, (string) $request->input('current_password'), 'profile');
        }

        $user->forceFill(['name' => $request->validated('name'), 'email' => $email])->save();
        SecurityAudit::record('admin.account.profile_updated', [
            'user_id' => $user->getKey(),
            'email_changed' => $emailChanged,
        ]);

        return back()->with('success', __('account.flash.profile'));
    }

    public function updatePassword(UpdateAccountPasswordRequest $request): RedirectResponse
    {
        $user = $this->administrator($request);
        $this->confirmCurrentPassword($user, (string) $request->input('current_password'), 'password');

        $user->forceFill([
            'password' => (string) $request->validated('password'),
            'remember_token' => Str::random(60),
            'auth_version' => $user->auth_version + 1,
        ])->save();

        // Les autres sessions deviennent invalides (auth_version) et, avec le pilote database, sont supprimées.
        $request->session()->put('auth_version', $user->auth_version);
        $request->session()->regenerate();
        AdminSessions::revokeOthers($user, $request->session());
        SecurityAudit::record('admin.account.password_changed', ['user_id' => $user->getKey()]);

        return back()->with('success', __('account.flash.password'));
    }

    private function administrator(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isAdministrator(), 403);

        return $user;
    }

    /** Vérifie le mot de passe actuel avec limitation des essais ; message identique quelle que soit la cause. */
    private function confirmCurrentPassword(User $user, string $password, string $scope): void
    {
        $key = 'admin-account:'.$scope.':'.$user->getKey();
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'current_password' => __('account.validation.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        if ($password === '' || ! Hash::check($password, $user->password)) {
            RateLimiter::hit($key, 300);
            SecurityAudit::record('admin.account.password_check_failed', ['user_id' => $user->getKey(), 'scope' => $scope]);
            throw ValidationException::withMessages(['current_password' => __('account.validation.current_wrong')]);
        }

        RateLimiter::clear($key);
    }
}
