<?php

declare(strict_types=1);

namespace Modules\Career\Policies;

use App\Models\User;

final class CareerContentPolicy
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
