<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\PreferredLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/** Applique la langue résolue (cookie, puis Accept-Language) aux messages serveur du panneau de compte. */
final class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale(PreferredLocale::fromRequest($request));

        return $next($request);
    }
}
