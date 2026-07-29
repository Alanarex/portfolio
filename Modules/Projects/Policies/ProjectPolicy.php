<?php

declare(strict_types=1);

namespace Modules\Projects\Policies;

use App\Models\User;
use Modules\Projects\Models\Project;

final class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function view(User $user, Project $project): bool
    {
        return $user->isAdministrator();
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdministrator();
    }
}
