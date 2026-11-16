<?php

declare(strict_types=1);

namespace App\Core\Security;

/** Masque en partie une adresse IP pour l'affichage et le journal (IPv4 : 2 derniers octets ; IPv6 : au-delà du /32). */
final class IpMask
{
    public static function mask(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $parts = explode('.', $ip);

            return $parts[0].'.'.$parts[1].'.•.•';
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            $packed = inet_pton($ip);
            if ($packed === false) {
                return null;
            }
            $groups = str_split(bin2hex($packed), 4);

            return $groups[0].':'.$groups[1].':•:•';
        }

        return null;
    }
}
