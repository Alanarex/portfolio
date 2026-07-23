<?php

declare(strict_types=1);

namespace Modules\Profile\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Profile\Application\SaveProfile;
use Modules\Profile\Http\Requests\UpdateProfileRequest;
use Modules\Profile\Models\Profile;

final class ProfileController extends Controller
{
    public function edit(): Response
    {
        Gate::authorize('viewAny', Profile::class);
        $profile = Profile::query()->where('key', 'main')->with(['translations', 'cvVersions'])->first();
        $translations = collect(['fr', 'en', 'ar'])->mapWithKeys(function (string $locale) use ($profile): array {
            $translation = $profile?->translations->firstWhere('locale', $locale);

            return [$locale => [
                'professional_titles' => $translation->professional_titles ?? [],
                'summary' => $translation->summary ?? '',
                'location_label' => $translation->location_label ?? '',
                'availability' => $translation->availability ?? '',
                'biography' => $translation->biography ?? '',
            ]];
        });

        return Inertia::render('Admin/Profile/Edit', [
            'profile' => [
                'display_name' => $profile->display_name ?? '',
                'is_published' => $profile->is_published ?? false,
                'show_location' => $profile->show_location ?? false,
                'show_availability' => $profile->show_availability ?? false,
                'translations' => $translations,
            ],
            'cvVersions' => $profile?->cvVersions->map(fn ($version): array => [
                'id' => $version->getKey(),
                'locale' => $version->locale,
                'label' => $version->label,
                'version_label' => $version->version_label,
                'original_filename' => $version->original_filename,
                'mime_type' => $version->mime_type,
                'size_bytes' => $version->size_bytes,
                'checksum_sha256' => $version->checksum_sha256,
                'is_verified' => $version->is_verified,
                'published' => $version->published_at !== null,
                'archived' => $version->archived_at !== null,
            ])->values() ?? [],
        ]);
    }

    public function update(UpdateProfileRequest $request, SaveProfile $saveProfile): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        $saveProfile->execute(
            data: $request->validated(),
            actor: $user,
            requestId: $request->attributes->get('request_id'),
        );

        return back()->with('success', 'Profil enregistré.');
    }
}
