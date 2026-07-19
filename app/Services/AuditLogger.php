<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AuditLogger
{
    public function record(string $action, ?AppUser $actor = null, ?object $subject = null, array $metadata = [], ?Request $request = null): void
    {
        $safeMetadata = Arr::except($metadata, ['password', 'token', 'secret', 'authorization', 'client_secret']);

        AuditLog::query()->create([
            'actor_core_user_id' => $actor?->core_user_id,
            'actor_role' => $actor?->role,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $metadata['description'] ?? null,
            'safe_metadata' => $safeMetadata ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
            'created_at' => now(),
        ]);
    }
}
