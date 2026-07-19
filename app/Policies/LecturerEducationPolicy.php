<?php

namespace App\Policies;

use App\Models\AppUser;
use App\Models\LecturerEducation;

class LecturerEducationPolicy
{
    public function view(AppUser $user, LecturerEducation $education): bool
    {
        return $user->isAdmin() || (string) $education->lecturer_core_id === (string) $user->core_lecturer_id;
    }

    public function create(AppUser $user): bool
    {
        return $user->isDosen() && filled($user->core_lecturer_id);
    }

    public function update(AppUser $user, LecturerEducation $education): bool
    {
        return $this->view($user, $education)
            && $user->isDosen()
            && $education->source_type === 'MANUAL'
            && in_array($education->verification_status, ['DRAFT', 'REVISION_REQUIRED'], true);
    }

    public function delete(AppUser $user, LecturerEducation $education): bool
    {
        return $this->update($user, $education);
    }

    public function verify(AppUser $user, LecturerEducation $education): bool
    {
        return $user->isAdmin() && $education->verification_status !== 'VERIFIED';
    }
}
