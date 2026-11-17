<?php

declare(strict_types=1);

namespace Modules\Profile\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Profile\Http\Requests\SavePortraitRequest;
use Modules\Profile\Infrastructure\PortraitStorage;
use Modules\Profile\Infrastructure\PublishedPortrait;
use Modules\Profile\Models\Profile;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PortraitController extends Controller
{
    /** Aperçu réservé à l'administration : visible même si le profil n'est pas publié. */
    public function show(PortraitStorage $storage): StreamedResponse
    {
        $profile = $this->profile();
        abort_if($profile === null || $profile->portrait_path === null, 404);
        Gate::authorize('viewAny', Profile::class);

        $disk = Storage::disk($storage->disk());
        abort_unless($disk->exists($profile->portrait_path), 404);

        return $disk->response($profile->portrait_path, null, [
            'Content-Type' => PortraitStorage::MIME_TYPES[pathinfo($profile->portrait_path, PATHINFO_EXTENSION)] ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function store(SavePortraitRequest $request, PortraitStorage $storage, AuditRecorder $auditRecorder): RedirectResponse
    {
        $profile = $this->profile();
        if ($profile === null) {
            return back()->withErrors(['portrait' => 'Enregistrez d’abord le profil avant d’ajouter une photo.']);
        }
        Gate::authorize('update', $profile);

        $file = $request->file('portrait');
        abort_unless($file instanceof \Illuminate\Http\UploadedFile, 422);

        $previous = $profile->portrait_path;
        $stored = $storage->store($file);
        $profile->forceFill(['portrait_path' => $stored['path'], 'portrait_checksum' => $stored['checksum']])->save();
        $storage->delete($previous);
        Cache::forget(PublishedPortrait::CACHE_KEY);

        $auditRecorder->record($request->user(), 'profile.portrait_updated', 'profile', 'main', ['portrait'], $request->attributes->get('request_id'));

        return back()->with('success', 'Photo enregistrée.');
    }

    public function destroy(Request $request, PortraitStorage $storage, AuditRecorder $auditRecorder): RedirectResponse
    {
        $profile = $this->profile();
        abort_if($profile === null, 404);
        Gate::authorize('update', $profile);

        $previous = $profile->portrait_path;
        $profile->forceFill(['portrait_path' => null, 'portrait_checksum' => null])->save();
        $storage->delete($previous);
        Cache::forget(PublishedPortrait::CACHE_KEY);

        if ($previous !== null) {
            $auditRecorder->record($request->user(), 'profile.portrait_removed', 'profile', 'main', ['portrait'], $request->attributes->get('request_id'));
        }

        return back()->with('success', 'Photo par défaut rétablie.');
    }

    private function profile(): ?Profile
    {
        return Profile::query()->where('key', 'main')->first();
    }
}
