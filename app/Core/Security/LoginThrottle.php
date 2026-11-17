<?php

declare(strict_types=1);

namespace App\Core\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Verrouillage de la connexion : après MAX_ATTEMPTS échecs pour un couple identifiant + IP, la connexion est
 * refusée pendant un délai croissant (1, 5, 15 puis 60 minutes selon le nombre de verrouillages des dernières 24 h).
 * Un second compteur, par IP seule, freine le balayage de plusieurs identifiants.
 * Le comportement est identique que le compte existe ou non : la clé dépend uniquement de la saisie.
 */
final class LoginThrottle
{
    public const MAX_ATTEMPTS = 3;

    public const IP_MAX_ATTEMPTS = 10;

    /** Délais de verrouillage successifs, en secondes. */
    private const LOCK_STEPS = [60, 300, 900, 3600];

    private const WINDOW = 900;

    public static function normalize(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private static function key(string $email, ?string $ip): string
    {
        return 'login:'.hash('sha256', self::normalize($email).'|'.$ip);
    }

    private static function ipKey(?string $ip): string
    {
        return 'login-ip:'.hash('sha256', (string) $ip);
    }

    /** Secondes restantes avant la prochaine tentative autorisée (0 = autorisé). */
    public static function lockedFor(Request $request, string $email): int
    {
        $until = (int) Cache::get(self::key($email, $request->ip()).':until', 0);
        $remaining = max(0, $until - now()->getTimestamp());
        if ($remaining === 0 && RateLimiter::tooManyAttempts(self::ipKey($request->ip()), self::IP_MAX_ATTEMPTS)) {
            return RateLimiter::availableIn(self::ipKey($request->ip()));
        }

        return $remaining;
    }

    /** Enregistre un échec ; renvoie true si cet échec déclenche un verrouillage. */
    public static function fail(Request $request, string $email): bool
    {
        RateLimiter::hit(self::ipKey($request->ip()), self::WINDOW);
        $key = self::key($email, $request->ip());
        $attempts = RateLimiter::hit($key, self::WINDOW);
        if ($attempts < self::MAX_ATTEMPTS) {
            return false;
        }

        $strikes = (int) Cache::get($key.':strikes', 0);
        $delay = self::LOCK_STEPS[min($strikes, count(self::LOCK_STEPS) - 1)];
        Cache::put($key.':until', now()->getTimestamp() + $delay, $delay);
        Cache::put($key.':strikes', $strikes + 1, 86_400);
        RateLimiter::clear($key);

        return true;
    }

    public static function succeed(Request $request, string $email): void
    {
        $key = self::key($email, $request->ip());
        RateLimiter::clear($key);
        Cache::forget($key.':until');
        Cache::forget($key.':strikes');
    }
}
