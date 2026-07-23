<?php

declare(strict_types=1);

namespace Modules\Profile\Policies;

use App\Models\User;
use Modules\Profile\Models\Profile;

final class ProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, Profile $profile): bool
    {
        return $user->isAdministrator();
    }
}
