<?php

namespace App\Services\Integration\Handlers;

use App\Contracts\IntegrationEventHandler;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\InboxItem;
use App\Models\IntegrationEvent;
use Illuminate\Support\Facades\DB;

class TuLetterPublishedHandler extends BaseIntegrationHandler implements IntegrationEventHandler
{
    public function handle(IntegrationEvent $event): array
    {
        $data = $this->validate($event, [
            'lecturer_core_id' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['nullable', 'string', 'max:100'],
            'filename' => ['required', 'string', 'max:255'],
            'path' => ['nullable', 'string', 'max:1000'],
            'mime_type' => ['nullable', 'string', 'max:255'],
            'size_bytes' => ['nullable', 'integer', 'min:0'],
            'sha256_checksum' => ['nullable', 'string', 'size:64'],
        ]);

        $safePath = $this->rejectUnsafePath($data['path'] ?? 'integrations/'.$event->source_app.'/'.$event->source_record_id.'/'.$data['filename']);

        return DB::transaction(function () use ($event, $data, $safePath): array {
            $document = Document::query()->firstOrCreate(
                [
                    'source_app' => $event->source_app,
                    'source_record_id' => $event->source_record_id,
                    'lecturer_core_id' => $data['lecturer_core_id'],
                ],
                [
                    'document_type' => strtoupper($data['document_type'] ?? 'SURAT_TUGAS'),
                    'title' => $data['title'],
                    'disk' => config('dosen_farmasi.documents.disk'),
                    'path' => $safePath,
                    'original_filename' => $data['filename'],
                    'stored_filename' => basename($safePath),
                    'extension' => strtolower(pathinfo($data['filename'], PATHINFO_EXTENSION) ?: 'pdf'),
                    'mime_type' => $data['mime_type'] ?? 'application/pdf',
                    'size_bytes' => $data['size_bytes'] ?? 0,
                    'sha256_checksum' => $data['sha256_checksum'] ?? str_repeat('0', 64),
                    'verification_status' => 'OFFICIAL',
                    'visibility' => 'INTERNAL',
                ],
            );

            DocumentVersion::query()->firstOrCreate(
                ['document_id' => $document->id, 'version_number' => 1],
                [
                    'disk' => $document->disk,
                    'path' => $document->path,
                    'original_filename' => $document->original_filename,
                    'mime_type' => $document->mime_type,
                    'size_bytes' => $document->size_bytes,
                    'sha256_checksum' => $document->sha256_checksum,
                    'source_app' => $event->source_app,
                    'source_record_id' => $event->source_record_id,
                    'created_at' => now(),
                ],
            );

            InboxItem::query()
                ->where('source_app', $event->source_app)
                ->where('source_record_id', $event->source_record_id)
                ->where('lecturer_core_id', $data['lecturer_core_id'])
                ->update(['document_id' => $document->id, 'status' => 'UNREAD']);

            $this->notify($data['lecturer_core_id'], 'Dokumen resmi baru', $document->title, [
                'document_id' => $document->id,
                'category' => 'document',
                'dedupe_key' => $event->event_id.':document',
            ]);

            return ['summary' => 'TU letter published document metadata registered.', 'related_records' => ['document_id' => $document->id]];
        });
    }
}
