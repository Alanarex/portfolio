<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Models\AdminLoginEvent;
use Illuminate\Http\Request;

final class LoginJournal
{
    public static function record(Request $request, bool $succeeded, ?int $userId, ?string $reason = null): void
    {
        AdminLoginEvent::query()->create([
            'user_id' => $userId,
            'succeeded' => $succeeded,
            'reason' => $reason,
            'ip_masked' => IpMask::mask($request->ip()),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
        ]);

        $threshold = AdminLoginEvent::query()->orderByDesc('id')->skip(AdminLoginEvent::KEEP)->value('id');
        if ($threshold !== null) {
            AdminLoginEvent::query()->where('id', '<=', $threshold)->delete();
        }
    }
}
