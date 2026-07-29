<?php

declare(strict_types=1);

namespace Modules\Projects\Policies;

use App\Models\User;
use Modules\Projects\Models\MediaAsset;

final class MediaAssetPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, MediaAsset $mediaAsset): bool
    {
        return $user->isAdministrator();
    }
}
