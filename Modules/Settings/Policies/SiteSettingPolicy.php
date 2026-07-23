<?php

declare(strict_types=1);

namespace Modules\Settings\Policies;

use App\Models\User;
use Modules\Settings\Models\SiteSetting;

final class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, SiteSetting $siteSetting): bool
    {
        return $user->isAdministrator();
    }
}
