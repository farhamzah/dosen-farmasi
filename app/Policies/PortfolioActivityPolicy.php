<?php

namespace App\Policies;

use App\Models\AppUser;
use App\Models\PortfolioActivity;

class PortfolioActivityPolicy
{
    public function view(AppUser $user, PortfolioActivity $activity): bool
    {
        return $user->isAdmin() || (string) $activity->lecturer_core_id === (string) $user->core_lecturer_id;
    }

    public function update(AppUser $user, PortfolioActivity $activity): bool
    {
        return $this->view($user, $activity)
            && ! $user->isAdmin()
            && $activity->isEditableByDosen();
    }

    public function managePersonalData(AppUser $user, PortfolioActivity $activity): bool
    {
        return $this->view($user, $activity) && ! in_array($activity->verification_status, ['ARCHIVED', 'CANCELLED'], true);
    }

    public function verify(AppUser $user, PortfolioActivity $activity): bool
    {
        return $user->isAdmin() && $activity->verification_status === 'SUBMITTED';
    }

    public function archive(AppUser $user, PortfolioActivity $activity): bool
    {
        return $user->isAdmin() && $activity->verification_status === 'ADMIN_VERIFIED';
    }

    public function cancelSystemVerified(AppUser $user, PortfolioActivity $activity): bool
    {
        return $user->isAdmin() && $activity->verification_status === 'SYSTEM_VERIFIED';
    }

    public function delete(AppUser $user, PortfolioActivity $activity): bool
    {
        return $this->update($user, $activity) && ! $activity->isSystemVerified();
    }
}
