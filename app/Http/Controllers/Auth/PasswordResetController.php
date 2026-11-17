<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Core\Security\AdminSessions;
use App\Core\Security\LoginJournal;
use App\Core\Security\SecurityAudit;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdministratorPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Mot de passe oublié de l'administrateur. La réponse à la demande est strictement identique que l'adresse
 * existe ou non ; le jeton (haché en base) est à usage unique et le lien est signé et expire rapidement.
 */
final class PasswordResetController extends Controller
{
    public function forgot(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $email = Str::lower(trim((string) $request->validate(['email' => ['required', 'string', 'email', 'max:255']])['email']));

        // Le résultat du broker (envoyé, inconnu, trop fréquent) n'est volontairement jamais exposé.
        Password::sendResetLink(['email' => $email, 'is_administrator' => true]);
        LoginJournal::record($request, true, null, 'reset_requested');
        SecurityAudit::record('admin.password_reset.requested', ['request_id' => $request->attributes->get('request_id')]);

        return back()->with('success', __('auth.forgot.sent'));
    }

    public function reset(Request $request, string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => (string) $request->query('email'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:4096', 'confirmed', AdministratorPassword::rule()],
        ]);

        $status = Password::reset(
            ['email' => Str::lower(trim($data['email'])), 'is_administrator' => true, 'token' => $data['token'], 'password' => $data['password']],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                    'auth_version' => $user->auth_version + 1,
                ])->save();
                AdminSessions::revokeAll($user);
                event(new PasswordReset($user));
                LoginJournal::record(request(), true, $user->getKey(), 'reset_completed');
                SecurityAudit::record('admin.password_reset.completed', ['user_id' => $user->getKey()]);
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            SecurityAudit::record('admin.password_reset.rejected', ['request_id' => $request->attributes->get('request_id')]);
            // Même message pour jeton faux, expiré, déjà utilisé ou adresse inconnue.
            throw ValidationException::withMessages(['email' => __('auth.forgot.invalid')]);
        }

        return redirect()->route('login')->with('success', __('auth.forgot.done'));
    }
}
