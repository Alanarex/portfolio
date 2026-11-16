<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Security\AdminSessions;
use App\Core\Security\SecurityAudit;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class AccountSessionController extends Controller
{
    public function destroy(Request $request, string $handle): RedirectResponse
    {
        Gate::authorize('manage-account');
        $user = $this->administrator($request);

        $revoked = AdminSessions::revoke($user, $request->session(), $handle);
        if ($revoked) {
            SecurityAudit::record('admin.session.revoked', ['user_id' => $user->getKey()]);
        }

        return back()->with('success', $revoked ? __('account.flash.session_revoked') : __('account.flash.session_gone'));
    }

    /** Déconnecte tous les autres appareils : invalide leur session même hors pilote database (auth_version). */
    public function destroyOthers(Request $request): RedirectResponse
    {
        Gate::authorize('manage-account');
        $user = $this->administrator($request);

        $user->forceFill(['auth_version' => $user->auth_version + 1])->save();
        $request->session()->put('auth_version', $user->auth_version);
        AdminSessions::revokeOthers($user, $request->session());
        SecurityAudit::record('admin.session.revoked_others', ['user_id' => $user->getKey()]);

        return back()->with('success', __('account.flash.others_revoked'));
    }

    private function administrator(Request $request): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isAdministrator(), 403);

        return $user;
    }
}
