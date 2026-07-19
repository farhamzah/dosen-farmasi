<?php

namespace App\Services;

use App\Models\AppUser;
use App\Models\InboxItem;
use App\Models\InboxRecipient;
use App\Notifications\DosenDatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InboxWorkflowService
{
    private const ALLOWED = [
        'UNREAD' => ['READ', 'ACCEPTED', 'DECLINED', 'ARCHIVED', 'CANCELLED'],
        'READ' => ['UNREAD', 'ACCEPTED', 'DECLINED', 'COMPLETED', 'ARCHIVED', 'CANCELLED'],
        'ACCEPTED' => ['COMPLETED', 'ARCHIVED', 'CANCELLED'],
        'DECLINED' => ['ARCHIVED', 'CANCELLED'],
        'COMPLETED' => ['ARCHIVED'],
        'CANCELLED' => ['ARCHIVED'],
        'ARCHIVED' => [],
    ];

    public function __construct(private readonly AuditLogger $audit, private readonly SafeUrlValidator $urls) {}

    public function createManual(array $data, AppUser $actor): InboxItem
    {
        $recipientIds = array_values(array_unique(array_map('strval', $data['lecturer_core_ids'] ?? [$data['lecturer_core_id'] ?? null])));
        $recipientIds = array_values(array_filter($recipientIds));

        if ($recipientIds === []) {
            throw ValidationException::withMessages(['lecturer_core_ids' => 'Penerima wajib dipilih.']);
        }

        return DB::transaction(function () use ($data, $actor, $recipientIds): InboxItem {
            $groupId = (string) Str::uuid();
            $first = null;

            foreach ($recipientIds as $lecturerId) {
                $item = InboxItem::query()->create([
                    'group_id' => $groupId,
                    'lecturer_core_id' => $lecturerId,
                    'type' => $data['type'],
                    'title' => $data['title'],
                    'summary' => $data['summary'] ?? null,
                    'priority' => $data['priority'] ?? 'NORMAL',
                    'status' => 'UNREAD',
                    'due_at' => $data['due_at'] ?? null,
                    'occurred_at' => $data['occurred_at'] ?? now(),
                    'action_url' => $data['action_url'] ?? null,
                    'safe_action_url' => $this->urls->validate($data['action_url'] ?? null),
                    'document_id' => $data['document_id'] ?? null,
                    'source_app' => $data['source_app'] ?? null,
                    'source_record_id' => $data['source_record_id'] ?? null,
                    'metadata' => $data['metadata'] ?? null,
                ]);

                InboxRecipient::query()->create([
                    'inbox_item_id' => $item->id,
                    'lecturer_core_id' => $lecturerId,
                    'app_user_id' => AppUser::query()->where('core_lecturer_id', $lecturerId)->value('id'),
                    'status' => 'UNREAD',
                ]);

                $owner = AppUser::query()->where('core_lecturer_id', $lecturerId)->first();
                $owner?->notify(new DosenDatabaseNotification('Inbox baru', $item->title, [
                    'inbox_item_id' => $item->id,
                    'category' => 'inbox',
                ]));

                $first ??= $item;
            }

            $this->audit->record('inbox.created', $actor, $first, ['recipient_count' => count($recipientIds)]);

            return $first;
        });
    }

    public function transition(InboxItem $item, AppUser $actor, string $status): InboxItem
    {
        $status = strtoupper($status);
        $from = $item->status;

        if (! in_array($status, self::ALLOWED[$from] ?? [], true)) {
            throw ValidationException::withMessages(['status' => 'Transisi inbox tidak diizinkan.']);
        }

        if ($item->type !== 'INVITATION' && in_array($status, ['ACCEPTED', 'DECLINED', 'COMPLETED'], true)) {
            throw ValidationException::withMessages(['status' => 'Item informasi tidak memiliki aksi tersebut.']);
        }

        $updates = ['status' => $status];
        if ($status === 'READ') {
            $updates['read_at'] = now();
        }

        $item->update($updates);
        $item->recipients()->where('lecturer_core_id', $item->lecturer_core_id)->update([
            'status' => $status,
            'read_at' => $status === 'READ' ? now() : $item->read_at,
            'responded_at' => in_array($status, ['ACCEPTED', 'DECLINED', 'COMPLETED'], true) ? now() : null,
            'archived_at' => $status === 'ARCHIVED' ? now() : null,
        ]);

        $this->audit->record('inbox.status_changed', $actor, $item, ['from' => $from, 'to' => $status]);

        return $item->fresh();
    }
}
