<?php

namespace App\Services\Integration\Handlers;

use App\Models\AppUser;
use App\Models\IntegrationEvent;
use App\Notifications\DosenDatabaseNotification;
use App\Services\AuditLogger;
use App\Services\SafeUrlValidator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class BaseIntegrationHandler
{
    public function __construct(protected readonly AuditLogger $audit) {}

    protected function validate(IntegrationEvent $event, array $rules): array
    {
        return Validator::make($event->payload ?: [], $rules)->validate();
    }

    protected function owner(string $lecturerCoreId): ?AppUser
    {
        return AppUser::query()->where('core_lecturer_id', $lecturerCoreId)->first();
    }

    protected function notify(string $lecturerCoreId, string $title, string $message, array $payload): void
    {
        $owner = $this->owner($lecturerCoreId);

        if (! $owner) {
            return;
        }

        $existing = $owner->notifications()
            ->where('data', 'like', '%"dedupe_key":"'.($payload['dedupe_key'] ?? '').'"%')
            ->exists();

        if (($payload['dedupe_key'] ?? null) && $existing) {
            return;
        }

        $owner->notify(new DosenDatabaseNotification($title, $message, $payload));
    }

    protected function rejectUnsafePath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $path = str_replace('\\', '/', trim((string) $path));

        if (str_starts_with($path, '/') || str_contains($path, '../') || preg_match('/^[A-Za-z]:\//', $path)) {
            throw ValidationException::withMessages(['payload.path' => 'Path dokumen tidak aman.']);
        }

        return $path;
    }

    protected function safeUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        return app(SafeUrlValidator::class)->validate($url);
    }

    protected function isStale(IntegrationEvent $event, mixed $model): bool
    {
        return (int) ($model->source_revision ?? 0) > (int) $event->source_revision;
    }
}
