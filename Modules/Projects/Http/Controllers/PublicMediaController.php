<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Projects\Enums\MediaKind;
use Modules\Projects\Enums\PublicationStatus;
use Modules\Projects\Models\MediaAsset;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PublicMediaController extends Controller
{
    public function show(string $locale, string $deliveryKey): StreamedResponse
    {
        abort_unless(in_array($locale, ['fr', 'en'], true), 404);

        $asset = MediaAsset::query()
            ->where('uuid', $deliveryKey)
            ->whereIn('kind', [
                MediaKind::Image->value,
                MediaKind::Screenshot->value,
                MediaKind::Poster->value,
            ])
            ->where('is_public', true)
            ->whereNull('deletion_pending_at')
            ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
            ->whereHas('project', fn ($query) => $query
                ->where('publication_status', PublicationStatus::Published->value)
                ->whereNull('deletion_pending_at')
                ->whereHas('translations', fn ($translationQuery) => $translationQuery->where('locale', $locale)))
            ->firstOrFail();

        abort_unless(Storage::disk($asset->disk)->exists($asset->path), 404);

        return Storage::disk($asset->disk)->response(
            $asset->path,
            null,
            [
                'Cache-Control' => 'public, max-age=3600, stale-while-revalidate=86400',
                'Content-Security-Policy' => "default-src 'none'; sandbox",
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
