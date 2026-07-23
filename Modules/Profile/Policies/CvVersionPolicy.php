<?php

declare(strict_types=1);

namespace Modules\Profile\Policies;

use App\Models\User;
use Modules\Profile\Models\CvVersion;

final class CvVersionPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, CvVersion $cvVersion): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, CvVersion $cvVersion): bool
    {
        return $user->isAdministrator();
    }
}
