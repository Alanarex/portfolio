<?php

declare(strict_types=1);

namespace Modules\Skills\Policies;

use App\Models\User;

final class SkillsContentPolicy
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
