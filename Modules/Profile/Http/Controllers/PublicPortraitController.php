<?php

declare(strict_types=1);

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Profile\Infrastructure\PortraitStorage;
use Modules\Profile\Models\Profile;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PublicPortraitController extends Controller
{
    public function __invoke(string $version, PortraitStorage $storage): StreamedResponse
    {
        $profile = Profile::query()->where('key', 'main')->where('is_published', true)->first();
        abort_if($profile === null || $profile->portrait_path === null || $profile->portrait_checksum === null, 404);
        abort_unless(hash_equals(substr($profile->portrait_checksum, 0, 16), $version), 404);

        $disk = Storage::disk($storage->disk());
        abort_unless($disk->exists($profile->portrait_path), 404);

        $extension = pathinfo($profile->portrait_path, PATHINFO_EXTENSION);

        return $disk->response($profile->portrait_path, null, [
            'Content-Type' => PortraitStorage::MIME_TYPES[$extension] ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }
}
