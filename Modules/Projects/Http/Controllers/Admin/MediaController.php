<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Projects\Application\DeleteMedia;
use Modules\Projects\Application\StoreMedia;
use Modules\Projects\Application\UpdateMedia;
use Modules\Projects\Http\Requests\StoreMediaRequest;
use Modules\Projects\Http\Requests\UpdateMediaRequest;
use Modules\Projects\Models\MediaAsset;

final class MediaController extends Controller
{
    public function store(StoreMediaRequest $request, StoreMedia $storeMedia): RedirectResponse
    {
        Gate::authorize('create', MediaAsset::class);
        $user = $request->user();
        $file = $request->file('file');
        abort_unless($user !== null && $file !== null, 403);
        $storeMedia->execute(
            $request->safe()->except('file'),
            $file,
            $user,
            $request->attributes->get('request_id'),
        );

        return back()->with('success', 'Média téléversé.');
    }

    public function update(
        UpdateMediaRequest $request,
        MediaAsset $mediaAsset,
        UpdateMedia $updateMedia,
    ): RedirectResponse {
        Gate::authorize('update', $mediaAsset);
        $user = $request->user();
        abort_unless($user !== null, 403);
        $updateMedia->execute(
            $mediaAsset,
            $request->validated(),
            $user,
            $request->attributes->get('request_id'),
        );

        return back()->with('success', 'Métadonnées du média enregistrées.');
    }

    public function destroy(
        Request $request,
        MediaAsset $mediaAsset,
        DeleteMedia $deleteMedia,
    ): RedirectResponse {
        Gate::authorize('delete', $mediaAsset);
        $user = $request->user();
        abort_unless($user !== null, 403);
        $deleteMedia->execute($mediaAsset, $user, $request->attributes->get('request_id'));

        return back()->with('success', 'Média supprimé.');
    }
}
