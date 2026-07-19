<?php

namespace App\Policies;

use App\Models\AppUser;
use App\Models\DocumentVersion;

class DocumentVersionPolicy
{
    public function view(AppUser $user, DocumentVersion $documentVersion): bool
    {
        $document = $documentVersion->document;

        return $document && ($user->isAdmin() || (string) $document->lecturer_core_id === (string) $user->core_lecturer_id);
    }
}
