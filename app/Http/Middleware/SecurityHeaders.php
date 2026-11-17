<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité sur toutes les réponses applicatives.
 *
 * CSP stricte : aucun script inline sans nonce (le nonce est transmis à Vite et aux scripts inline des vues),
 * pas d'eval, pas d'objet, pas de cadre, formulaires et base limités à l'origine. 'wasm-unsafe-eval' est requis
 * par le décodeur Meshopt (WebAssembly) du modèle 3D ; les styles inline restent permis (attributs style
 * des vues, barre de progression d'Inertia) car ils ne permettent pas d'exécuter du code.
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Doit être appelé avant le rendu des vues : @vite et les scripts inline lisent ce nonce.
        Vite::useCspNonce();

        $response = $next($request);
        $headers = $response->headers;

        $headers->set((bool) config('security.csp_report_only') ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy', $this->policy());
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=(), interest-cohort=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if ($request->isSecure() && (bool) config('security.hsts')) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Les pages privées (connexion, réinitialisation, tableau de bord) ne doivent jamais être mises en cache.
        if ($request->is('login', 'dashboard', 'dashboard/*', 'forgot-password', 'reset-password', 'reset-password/*')) {
            $headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }

    private function policy(): string
    {
        $nonce = Vite::cspNonce();
        $dev = $this->devServer();

        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'nonce-{$nonce}'", "'wasm-unsafe-eval'", ...$dev],
            'style-src' => ["'self'", "'unsafe-inline'", ...$dev],
            'img-src' => ["'self'", 'data:', 'blob:', ...$dev],
            'font-src' => ["'self'", 'data:', ...$dev],
            'connect-src' => ["'self'", 'blob:', 'data:', ...$dev, ...array_map(fn (string $h): string => str_replace('http', 'ws', $h), $dev)],
            'worker-src' => ["'self'", 'blob:'],
            'media-src' => ["'self'", 'blob:'],
            'manifest-src' => ["'self'"],
            'object-src' => ["'none'"],
            'frame-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'form-action' => ["'self'"],
            'frame-ancestors' => ["'none'"],
        ];

        $parts = [];
        foreach ($directives as $name => $sources) {
            $parts[] = $name.' '.implode(' ', $sources);
        }
        if (app()->isProduction()) {
            $parts[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $parts);
    }

    /** @return list<string> Origine du serveur Vite en développement (vide en production). */
    private function devServer(): array
    {
        if (app()->isProduction() || ! Vite::isRunningHot()) {
            return [];
        }

        $url = trim((string) @file_get_contents(Vite::hotFile()));

        return preg_match('#^https?://[\w.\-\[\]:]+#', $url, $m) === 1 ? [$m[0]] : [];
    }
}
