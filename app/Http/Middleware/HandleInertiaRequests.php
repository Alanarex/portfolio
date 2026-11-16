<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\PreferredLocale;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'admin';

    /** @return array<string, mixed> */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'locale' => fn (): string => PreferredLocale::fromRequest($request),
            'auth' => [
                'user' => $request->user()?->only(['id', 'name']),
            ],
            'flash' => [
                'success' => fn (): ?string => $request->session()->get('success'),
            ],
        ];
    }
}
