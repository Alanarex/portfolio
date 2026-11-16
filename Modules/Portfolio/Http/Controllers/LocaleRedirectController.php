<?php

declare(strict_types=1);

namespace Modules\Portfolio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\PreferredLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Racine et URL sans préfixe de langue : redirection 302 (la réponse varie selon le visiteur, jamais 301)
 * vers /fr ou /en. Les URL déjà préfixées ne passent jamais par ici.
 */
final class LocaleRedirectController extends Controller
{
    public function __invoke(Request $request, string $path = ''): RedirectResponse
    {
        $locale = PreferredLocale::fromRequest($request);
        $target = '/'.$locale.($path === '' ? '' : '/'.$path);
        $query = $request->getQueryString();

        return redirect()->to($query === null ? $target : $target.'?'.$query, 302)
            ->header('Vary', 'Accept-Language, Cookie, User-Agent')
            ->header('Cache-Control', 'no-store, private');
    }
}
