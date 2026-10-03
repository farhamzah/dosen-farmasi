<?php

namespace App\Services\Integration\Handlers;

use App\Contracts\IntegrationEventHandler;
use App\Exceptions\IntegrationProcessingException;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\InboxItem;
use App\Models\IntegrationEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TuLetterPublishedHandler extends BaseIntegrationHandler implements IntegrationEventHandler
{
    public function handle(IntegrationEvent $event): array
    {
        $data = $this->validate($event, [
            'lecturer_core_id' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['nullable', 'string', 'max:100'],
            'filename' => ['required', 'string', 'max:255'],
            'path' => ['required', 'string', 'max:1000'],
            'mime_type' => ['nullable', 'string', 'max:255'],
            'size_bytes' => ['nullable', 'integer', 'min:0'],
            'sha256_checksum' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/i'],
        ]);

        $safePath = $this->rejectUnsafePath($data['path']);
        $disk = (string) config('dosen_farmasi.documents.disk');
        $storage = Storage::disk($disk);

        if (! $storage->exists($safePath)) {
            throw new IntegrationProcessingException('Berkas surat TU belum tersedia pada storage bersama.', 'DOCUMENT_NOT_FOUND');
        }

        if (! hash_equals(strtolower($data['sha256_checksum']), strtolower(hash('sha256', $storage->get($safePath))))) {
            throw new IntegrationProcessingException('Checksum berkas surat TU tidak cocok.', 'DOCUMENT_CHECKSUM_MISMATCH');
        }

        return DB::transaction(function () use ($event, $data, $safePath, $disk, $storage): array {
            $document = Document::query()->firstOrCreate(
                [
                    'source_app' => $event->source_app,
                    'source_record_id' => $event->source_record_id,
                    'lecturer_core_id' => $data['lecturer_core_id'],
                ],
                [
                    'document_type' => strtoupper($data['document_type'] ?? 'SURAT_TUGAS'),
                    'title' => $data['title'],
                    'disk' => $disk,
                    'path' => $safePath,
                    'original_filename' => $data['filename'],
                    'stored_filename' => basename($safePath),
                    'extension' => strtolower(pathinfo($data['filename'], PATHINFO_EXTENSION) ?: 'pdf'),
                    'mime_type' => $data['mime_type'] ?? 'application/pdf',
                    'size_bytes' => $storage->size($safePath),
                    'sha256_checksum' => strtolower($data['sha256_checksum']),
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
