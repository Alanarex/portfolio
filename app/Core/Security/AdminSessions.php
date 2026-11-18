<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\DB;

/**
 * Sessions actives de l'administrateur, lisibles et révocables seulement avec le pilote `database`.
 * Un identifiant de session n'est jamais exposé : le client reçoit une empreinte opaque (HMAC de l'identifiant).
 */
final class AdminSessions
{
    public static function supported(): bool
    {
        return config('session.driver') === 'database';
    }

    public static function handle(string $sessionId): string
    {
        return substr(hash_hmac('sha256', $sessionId, (string) config('app.key')), 0, 32);
    }

    /** @return list<array{handle: string, browser: string, platform: string, device: string, ip: string|null, last_activity: string, current: bool}> */
    public static function list(User $user, Session $current): array
    {
        if (! self::supported()) {
            return [];
        }

        $rows = DB::table((string) config('session.table', 'sessions'))
            ->where('user_id', $user->getKey())
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity']);

        $sessions = [];
        foreach ($rows as $row) {
            $summary = UserAgentSummary::parse($row->user_agent);
            $sessions[] = [
                'handle' => self::handle((string) $row->id),
                ...$summary,
                'ip' => IpMask::mask($row->ip_address),
                'last_activity' => date('c', (int) $row->last_activity),
                'current' => (string) $row->id === $current->getId(),
            ];
        }

        return $sessions;
    }

    /** Révoque une session de l'utilisateur (jamais la session courante). Renvoie false si elle n'existe plus. */
    public static function revoke(User $user, Session $current, string $handle): bool
    {
        if (! self::supported()) {
            return false;
        }

        $table = (string) config('session.table', 'sessions');
        $ids = DB::table($table)->where('user_id', $user->getKey())->pluck('id');
        foreach ($ids as $id) {
            if (hash_equals(self::handle((string) $id), $handle) && (string) $id !== $current->getId()) {
                DB::table($table)->where('id', $id)->delete();

                return true;
            }
        }

        return false;
    }

    /** Supprime toutes les autres sessions de l'utilisateur. */
    public static function revokeOthers(User $user, Session $current): void
    {
        if (! self::supported()) {
            return;
        }

        DB::table((string) config('session.table', 'sessions'))
            ->where('user_id', $user->getKey())
            ->where('id', '!=', $current->getId())
            ->delete();
    }

    /** Supprime toutes les sessions de l'utilisateur (après une réinitialisation de mot de passe). */
    public static function revokeAll(User $user): void
    {
        if (! self::supported()) {
            return;
        }

        DB::table((string) config('session.table', 'sessions'))->where('user_id', $user->getKey())->delete();
    }
}
