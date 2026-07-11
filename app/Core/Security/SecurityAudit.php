<?php

declare(strict_types=1);

namespace App\Core\Security;

use Illuminate\Support\Facades\Log;

final class SecurityAudit
{
    /** @param array<string, bool|int|string|null> $context */
    public static function record(string $event, array $context = []): void
    {
        Log::info('security.audit', ['event' => $event, ...$context]);
    }
}
