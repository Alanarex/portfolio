<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Health\HealthChecker;
use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function __invoke(HealthChecker $health): JsonResponse
    {
        $result = $health->run();

        return response()
            ->json($result, $result['status'] === HealthChecker::OK ? 200 : 503)
            ->header('Cache-Control', 'no-store')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
