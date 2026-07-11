<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Core\Security\SecurityAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);
        $credentials['email'] = mb_strtolower(trim((string) $credentials['email']));
        $credentials['is_administrator'] = true;

        if (! Auth::attempt($credentials, false)) {
            SecurityAudit::record('admin.login.failed', ['request_id' => $request->attributes->get('request_id')]);
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }

        $request->session()->regenerate();
        $request->session()->put('auth_version', $request->user()?->auth_version);
        SecurityAudit::record('admin.login.succeeded', [
            'request_id' => $request->attributes->get('request_id'),
            'user_id' => $request->user()?->getKey(),
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $userId = $request->user()?->getKey();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        SecurityAudit::record('admin.logout', [
            'request_id' => $request->attributes->get('request_id'),
            'user_id' => $userId,
        ]);

        return redirect()->route('home');
    }
}
