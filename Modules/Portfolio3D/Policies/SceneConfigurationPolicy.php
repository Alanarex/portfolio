<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Policies;

use App\Models\User;

final class SceneConfigurationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user): bool
    {
        return $user->isAdministrator();
    }
}
