<?php

namespace App\Policies;

use App\Models\AppUser;
use App\Models\InboxItem;

class InboxItemPolicy
{
    public function view(AppUser $user, InboxItem $inboxItem): bool
    {
        return $user->isAdmin() || (string) $inboxItem->lecturer_core_id === (string) $user->core_lecturer_id;
    }

    public function update(AppUser $user, InboxItem $inboxItem): bool
    {
        return $this->view($user, $inboxItem);
    }
}
