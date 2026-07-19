<?php

namespace App\Policies;

use App\Models\AppUser;
use App\Models\PortfolioVerificationHistory;

class PortfolioVerificationHistoryPolicy
{
    public function view(AppUser $user, PortfolioVerificationHistory $history): bool
    {
        return $user->isAdmin() || (string) $history->activity?->lecturer_core_id === (string) $user->core_lecturer_id;
    }

    public function update(AppUser $user, PortfolioVerificationHistory $history): bool
    {
        return false;
    }

    public function delete(AppUser $user, PortfolioVerificationHistory $history): bool
    {
        return false;
    }
}
