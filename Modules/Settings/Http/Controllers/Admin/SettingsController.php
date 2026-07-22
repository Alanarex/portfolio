<?php

declare(strict_types=1);

namespace Modules\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Settings\Application\SaveSettings;
use Modules\Settings\Http\Requests\UpdateSettingsRequest;
use Modules\Settings\Models\Locale;
use Modules\Settings\Models\SiteSetting;

final class SettingsController extends Controller
{
    public function edit(): Response
    {
        Gate::authorize('viewAny', SiteSetting::class);
        $settings = SiteSetting::query()->where('key', 'main')->with(['socialLinks', 'featureFlags'])->first();
        $links = collect(['github', 'gitlab', 'linkedin', 'whatsapp', 'scheduling'])->map(function (string $platform, int $index) use ($settings): array {
            $link = $settings?->socialLinks->firstWhere('platform', $platform);

            return [
                'platform' => $platform,
                'url' => $link->url ?? '',
                'is_enabled' => $link->is_enabled ?? false,
                'is_public' => $link->is_public ?? false,
                'sort_order' => $link->sort_order ?? $index,
            ];
        });
        $flags = collect(['projects', 'contact', 'cv', 'three_d'])->mapWithKeys(function (string $key) use ($settings): array {
            if ($settings === null) {
                return [$key => false];
            }

            return [$key => (bool) ($settings->featureFlags->firstWhere('key', $key)->enabled ?? false)];
        });

        return Inertia::render('Admin/Settings/Edit', [
            'settings' => [
                'site_name' => $settings->site_name ?? config('app.name'),
                'default_locale' => 'fr',
                'contact_email' => $settings->contact_email ?? '',
                'contact_phone' => $settings->contact_phone ?? '',
                'show_email' => $settings->show_email ?? false,
                'show_phone' => $settings->show_phone ?? false,
                'social_links' => $links,
                'feature_flags' => $flags,
            ],
            'locales' => Locale::query()->orderBy('code')->get()->map(fn (Locale $locale): array => [
                'code' => $locale->code,
                'label' => $locale->label,
                'direction' => $locale->direction,
                'status' => $locale->status,
            ]),
        ]);
    }

    public function update(UpdateSettingsRequest $request, SaveSettings $saveSettings): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        $saveSettings->execute($request->validated(), $user, $request->attributes->get('request_id'));

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
