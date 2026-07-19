<?php

namespace App\Policies;

use App\Models\AppUser;
use App\Models\CalendarEvent;

class CalendarEventPolicy
{
    public function view(AppUser $user, CalendarEvent $calendarEvent): bool
    {
        return $user->isAdmin() || (string) $calendarEvent->lecturer_core_id === (string) $user->core_lecturer_id;
    }
}
