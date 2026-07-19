<?php

namespace App\Policies;

use App\Models\AppUser;
use App\Models\PortfolioActivity;
use App\Models\PortfolioParticipant;

class PortfolioParticipantPolicy
{
    public function create(AppUser $user, PortfolioActivity $activity): bool
    {
        return $user->isAdmin() || $activity->isEditableByDosen() && (string) $activity->lecturer_core_id === (string) $user->core_lecturer_id;
    }

    public function delete(AppUser $user, PortfolioParticipant $participant): bool
    {
        $activity = $participant->activity;

        return $activity && ($user->isAdmin() || $activity->isEditableByDosen() && (string) $activity->lecturer_core_id === (string) $user->core_lecturer_id);
    }
}
