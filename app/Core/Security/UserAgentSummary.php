<?php

declare(strict_types=1);

namespace App\Core\Security;

/** Résumé lisible d'un User-Agent (navigateur, système, type d'appareil), sans dépendance externe. */
final class UserAgentSummary
{
    /** @return array{browser: string, platform: string, device: string} */
    public static function parse(?string $userAgent): array
    {
        $ua = (string) $userAgent;

        $browser = match (true) {
            (bool) preg_match('/Edg(?:e|A|iOS)?\//', $ua) => 'Edge',
            (bool) preg_match('/OPR\/|Opera/', $ua) => 'Opera',
            (bool) preg_match('/Firefox\/|FxiOS\//', $ua) => 'Firefox',
            (bool) preg_match('/Chrome\/|CriOS\//', $ua) => 'Chrome',
            (bool) preg_match('/Safari\//', $ua) => 'Safari',
            default => '',
        };

        $platform = match (true) {
            (bool) preg_match('/iPhone|iPad|iPod/', $ua) => 'iOS',
            (bool) preg_match('/Android/', $ua) => 'Android',
            (bool) preg_match('/Windows/', $ua) => 'Windows',
            (bool) preg_match('/Mac OS X|Macintosh/', $ua) => 'macOS',
            (bool) preg_match('/CrOS/', $ua) => 'ChromeOS',
            (bool) preg_match('/Linux|X11/', $ua) => 'Linux',
            default => '',
        };

        $device = match (true) {
            (bool) preg_match('/iPad|Tablet/', $ua) => 'tablet',
            (bool) preg_match('/Mobile|iPhone|iPod|Android/', $ua) => 'mobile',
            $ua === '' => 'unknown',
            default => 'desktop',
        };

        return ['browser' => $browser, 'platform' => $platform, 'device' => $device];
    }
}
