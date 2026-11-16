<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Résout la langue d'un visiteur qui n'a pas d'URL préfixée : cookie de choix manuel, puis en-tête Accept-Language.
 *
 * Règles : un robot d'indexation reçoit toujours le français (c'est le x-default des balises hreflang) ;
 * sans en-tête exploitable, français ; avec un en-tête dont aucune langue n'est gérée (de, es…), anglais.
 */
final class PreferredLocale
{
    public const COOKIE = 'portfolio_locale';

    /** Une année, en secondes (lue par le JavaScript du sélecteur FR/EN). */
    public const COOKIE_LIFETIME = 31_536_000;

    /** @var list<string> */
    public const SUPPORTED = ['fr', 'en'];

    public const DEFAULT = 'fr';

    public const FALLBACK = 'en';

    private const CRAWLER_PATTERN = '/bot\b|bot[\/;)\s]|crawl|spider|slurp|mediapartners|facebookexternalhit|embedly|preview|lighthouse|pingdom|uptime/i';

    public static function fromRequest(Request $request): string
    {
        if (self::isCrawler((string) $request->userAgent())) {
            return self::DEFAULT;
        }

        $saved = $request->cookie(self::COOKIE);
        if (is_string($saved) && in_array($saved, self::SUPPORTED, true)) {
            return $saved;
        }

        return self::fromAcceptLanguage($request->headers->get('Accept-Language'));
    }

    public static function fromAcceptLanguage(?string $header): string
    {
        $candidates = [];
        foreach (explode(',', (string) $header) as $position => $part) {
            $pieces = array_map('trim', explode(';', $part));
            $tag = strtolower($pieces[0]);
            if ($tag === '' || $tag === '*') {
                continue;
            }

            $quality = 1.0;
            foreach (array_slice($pieces, 1) as $parameter) {
                if (preg_match('/^q=([0-9]*\.?[0-9]+)$/i', $parameter, $matches) === 1) {
                    $quality = min(1.0, (float) $matches[1]);
                }
            }

            if ($quality > 0.0) {
                $candidates[] = ['language' => explode('-', $tag)[0], 'quality' => $quality, 'position' => $position];
            }
        }

        if ($candidates === []) {
            return self::DEFAULT;
        }

        usort($candidates, static fn (array $a, array $b): int => [$b['quality'], $a['position']] <=> [$a['quality'], $b['position']]);
        foreach ($candidates as $candidate) {
            if (in_array($candidate['language'], self::SUPPORTED, true)) {
                return $candidate['language'];
            }
        }

        return self::FALLBACK;
    }

    public static function isCrawler(string $userAgent): bool
    {
        return $userAgent !== '' && preg_match(self::CRAWLER_PATTERN, $userAgent) === 1;
    }
}
