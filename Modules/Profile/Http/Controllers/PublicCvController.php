<?php

declare(strict_types=1);

namespace Modules\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Profile\Infrastructure\PublishedCvLocator;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PublicCvController extends Controller
{
    public function __invoke(string $locale, PublishedCvLocator $locator): StreamedResponse
    {
        abort_unless(in_array($locale, ['fr', 'en'], true), 404);
        app()->setLocale($locale);

        $version = $locator->find($locale);
        abort_if($version === null || $version->disk === null || $version->path === null, 404);

        $downloadName = sprintf('cv-%s-%s.pdf', $locale, $version->version_label);

        return Storage::disk($version->disk)->download(
            $version->path,
            $downloadName,
            [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
                'Content-Security-Policy' => "default-src 'none'; sandbox",
            ],
        );
    }
}
