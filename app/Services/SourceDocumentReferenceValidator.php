<?php

namespace App\Services;

use App\Exceptions\IntegrationProcessingException;

class SourceDocumentReferenceValidator
{
    public function validateMany(array $references): array
    {
        return collect($references)->map(fn (array $reference): array => $this->validate($reference))->all();
    }

    public function validate(array $reference): array
    {
        $disk = (string) ($reference['storage_disk_alias'] ?? $reference['disk'] ?? '');
        $path = str_replace('\\', '/', trim((string) ($reference['relative_path'] ?? $reference['path'] ?? '')));

        if (! in_array($disk, config('dosen_farmasi.documents.allowed_source_disks', ['shared_private', 'dosen_private']), true)) {
            throw new IntegrationProcessingException('Disk dokumen sumber tidak dikenal.', 'DOCUMENT_NOT_FOUND', false);
        }

        if ($path === '' || str_starts_with($path, '/') || str_contains($path, '../') || preg_match('/^[A-Za-z]:\//', $path)) {
            throw new IntegrationProcessingException('Path dokumen sumber tidak aman.', 'DOCUMENT_NOT_FOUND', false);
        }

        if (isset($reference['sha256']) && ! preg_match('/^[a-f0-9]{64}$/i', (string) $reference['sha256'])) {
            throw new IntegrationProcessingException('Checksum dokumen sumber tidak valid.', 'DOCUMENT_CHECKSUM_MISMATCH', false);
        }

        return array_merge($reference, [
            'storage_disk_alias' => $disk,
            'relative_path' => $path,
        ]);
    }
}
