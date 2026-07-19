<?php

namespace App\Policies;

use App\Models\AppUser;
use App\Models\PortfolioActivity;
use App\Models\Tag;

class TagPolicy
{
    public function viewAny(AppUser $user): bool
    {
        return true;
    }

    public function create(AppUser $user): bool
    {
        return $user->isAdmin();
    }

    public function update(AppUser $user, Tag $tag): bool
    {
        return $user->isAdmin();
    }

    public function delete(AppUser $user, Tag $tag): bool
    {
        return $user->isAdmin() && ! $tag->activities()->exists();
    }

    public function attach(AppUser $user, PortfolioActivity $activity): bool
    {
        return $user->isAdmin() || (string) $activity->lecturer_core_id === (string) $user->core_lecturer_id;
    }
}
