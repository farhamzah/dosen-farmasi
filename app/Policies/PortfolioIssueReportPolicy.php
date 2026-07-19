<?php

namespace App\Policies;

use App\Models\AppUser;
use App\Models\PortfolioActivity;
use App\Models\PortfolioIssueReport;

class PortfolioIssueReportPolicy
{
    public function view(AppUser $user, PortfolioIssueReport $issueReport): bool
    {
        return $user->isAdmin() || (string) $issueReport->activity?->lecturer_core_id === (string) $user->core_lecturer_id;
    }

    public function create(AppUser $user, PortfolioActivity $activity): bool
    {
        return ! $user->isAdmin()
            && $activity->isSystemVerified()
            && (string) $activity->lecturer_core_id === (string) $user->core_lecturer_id;
    }

    public function update(AppUser $user, PortfolioIssueReport $issueReport): bool
    {
        return $user->isAdmin();
    }
}
