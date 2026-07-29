<?php

declare(strict_types=1);

namespace Modules\Profile\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Profile\Application\SaveCvVersion;
use Modules\Profile\Http\Requests\SaveCvVersionRequest;
use Modules\Profile\Infrastructure\CvStorage;
use Modules\Profile\Models\CvVersion;
use Modules\Profile\Models\Profile;

final class CvVersionController extends Controller
{
    public function store(SaveCvVersionRequest $request, SaveCvVersion $saveCvVersion): RedirectResponse
    {
        Gate::authorize('create', CvVersion::class);
        $user = $request->user();
        abort_unless($user !== null, 403);
        $saveCvVersion->execute($request->validated(), $user, $request->attributes->get('request_id'));

        return back()->with('success', 'Version de CV ajoutée.');
    }

    public function update(
        SaveCvVersionRequest $request,
        CvVersion $cvVersion,
        SaveCvVersion $saveCvVersion,
    ): RedirectResponse {
        Gate::authorize('update', $cvVersion);
        $user = $request->user();
        abort_unless($user !== null, 403);
        $saveCvVersion->execute($request->validated(), $user, $request->attributes->get('request_id'), $cvVersion);

        return back()->with('success', 'Version de CV enregistrée.');
    }

    public function destroy(
        Request $request,
        CvVersion $cvVersion,
        AuditRecorder $auditRecorder,
        CvStorage $storage,
    ): RedirectResponse {
        Gate::authorize('delete', $cvVersion);
        $user = $request->user();
        abort_unless($user !== null, 403);

        $stored = DB::transaction(function () use ($request, $cvVersion, $auditRecorder, $user): array {
            $profile = Profile::query()->where('key', 'main')->lockForUpdate()->firstOrFail();
            $lockedVersion = CvVersion::query()
                ->where('profile_id', $profile->getKey())
                ->whereKey($cvVersion->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $id = (string) $lockedVersion->getKey();
            $disk = $lockedVersion->disk;
            $path = $lockedVersion->path;
            $lockedVersion->delete();
            $auditRecorder->record(
                actor: $user,
                action: 'cv-version.deleted',
                subjectType: 'cv-version',
                subjectId: $id,
                changedFields: [],
                requestId: $request->attributes->get('request_id'),
            );

            return compact('disk', 'path');
        });
        $storage->delete($stored['disk'], $stored['path']);

        return back()->with('success', 'Version de CV supprimée.');
    }
}
