<?php

namespace App\Policies;

use App\Models\AppUser;
use App\Models\Document;

class DocumentPolicy
{
    public function view(AppUser $user, Document $document): bool
    {
        return $user->isAdmin() || (string) $document->lecturer_core_id === (string) $user->core_lecturer_id;
    }

    public function delete(AppUser $user, Document $document): bool
    {
        if (! $this->view($user, $document) || $document->isOfficial()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $document->activities()
            ->whereIn('verification_status', ['DRAFT', 'REVISION_REQUIRED'])
            ->where('source_type', 'MANUAL')
            ->exists();
    }
}
