<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Core\Security\LoginJournal;
use App\Core\Security\LoginThrottle;
use App\Core\Security\SecurityAudit;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $credentials['email'] = LoginThrottle::normalize((string) $credentials['email']);
        $email = $credentials['email'];
        $credentials['is_administrator'] = true;

        // Verrouillé : on ne vérifie même pas le mot de passe, la réponse est la même que le compte existe ou non.
        $wait = LoginThrottle::lockedFor($request, $email);
        if ($wait > 0) {
            LoginJournal::record($request, false, null, 'locked');
            SecurityAudit::record('admin.login.locked_out', ['request_id' => $request->attributes->get('request_id')]);
            throw ValidationException::withMessages(['email' => $this->lockedMessage($wait)])->status(429);
        }

        if (! Auth::attempt($credentials, false)) {
            $known = User::query()->where('email', $email)->where('is_administrator', true)->value('id');
            if ($known === null) {
                // Égalise le temps de réponse avec un compte existant (le hachage a un coût constant).
                Hash::check((string) $credentials['password'], self::dummyHash());
            }
            $locked = LoginThrottle::fail($request, $email);
            LoginJournal::record($request, false, is_numeric($known) ? (int) $known : null, $locked ? 'locked' : 'failed');
            SecurityAudit::record('admin.login.failed', [
                'request_id' => $request->attributes->get('request_id'),
                'locked' => $locked,
            ]);
            $message = $locked ? $this->lockedMessage(LoginThrottle::lockedFor($request, $email)) : __('auth.failed');
            throw ValidationException::withMessages(['email' => $message])->status($locked ? 429 : 422);
        }

        LoginThrottle::succeed($request, $email);
        $request->session()->regenerate();
        $request->session()->put('auth_version', $request->user()?->auth_version);
        LoginJournal::record($request, true, $request->user()?->getKey());
        SecurityAudit::record('admin.login.succeeded', [
            'request_id' => $request->attributes->get('request_id'),
            'user_id' => $request->user()?->getKey(),
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function lockedMessage(int $seconds): string
    {
        return __('auth.throttle', ['minutes' => max(1, (int) ceil($seconds / 60))]);
    }

    private static function dummyHash(): string
    {
        static $hash = null;

        return $hash ??= Hash::make('dummy-password-for-timing');
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

        return redirect()->route('portfolio');
    }
}
