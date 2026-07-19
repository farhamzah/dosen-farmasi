<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class DocumentFileValidator
{
    public function validate(UploadedFile $file): array
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $allowed = config('dosen_farmasi.documents.allowed_extensions', []);

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages(['file' => 'Ekstensi dokumen tidak diizinkan.']);
        }

        $realPath = $file->getRealPath();
        if (! $realPath || ! is_readable($realPath)) {
            throw ValidationException::withMessages(['file' => 'File tidak dapat dibaca.']);
        }

        $contents = file_get_contents($realPath);
        if ($contents === false) {
            throw ValidationException::withMessages(['file' => 'File tidak dapat dibaca.']);
        }

        $serverMime = (string) ($file->getMimeType() ?: mime_content_type($realPath) ?: '');
        $allowedMimes = config("dosen_farmasi.documents.mime_by_extension.$extension", []);

        if ($allowedMimes !== [] && ! in_array($serverMime, $allowedMimes, true)) {
            throw ValidationException::withMessages(['file' => 'MIME dokumen tidak sesuai dengan ekstensi.']);
        }

        match ($extension) {
            'pdf' => $this->assertStartsWith($contents, '%PDF-', 'File PDF tidak valid.'),
            'jpg', 'jpeg' => $this->assertStartsWithBytes($contents, [0xFF, 0xD8, 0xFF], 'File JPEG tidak valid.'),
            'png' => $this->assertStartsWithBytes($contents, [0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A], 'File PNG tidak valid.'),
            'doc' => $this->assertOle($contents, 'File DOC tidak valid.'),
            'xls' => $this->assertOle($contents, 'File XLS tidak valid.'),
            'docx' => $this->assertOfficeZip($realPath, 'word/', 'File DOCX tidak valid.'),
            'xlsx' => $this->assertOfficeZip($realPath, 'xl/', 'File XLSX tidak valid.'),
            default => throw ValidationException::withMessages(['file' => 'Jenis file tidak didukung.']),
        };

        return [
            'extension' => $extension,
            'mime_type' => $serverMime,
            'contents' => $contents,
            'sha256' => hash_file('sha256', $realPath),
        ];
    }

    private function assertStartsWith(string $contents, string $prefix, string $message): void
    {
        if (! str_starts_with($contents, $prefix)) {
            throw ValidationException::withMessages(['file' => $message]);
        }
    }

    private function assertStartsWithBytes(string $contents, array $bytes, string $message): void
    {
        foreach ($bytes as $offset => $byte) {
            if (! isset($contents[$offset]) || ord($contents[$offset]) !== $byte) {
                throw ValidationException::withMessages(['file' => $message]);
            }
        }
    }

    private function assertOle(string $contents, string $message): void
    {
        $this->assertStartsWithBytes($contents, [0xD0, 0xCF, 0x11, 0xE0, 0xA1, 0xB1, 0x1A, 0xE1], $message);
    }

    private function assertOfficeZip(string $path, string $requiredPrefix, string $message): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw ValidationException::withMessages(['file' => 'Validasi dokumen Office membutuhkan ekstensi PHP zip.']);
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['file' => $message]);
        }

        $hasContentTypes = $zip->locateName('[Content_Types].xml') !== false;
        $hasRequiredFolder = false;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = (string) $zip->getNameIndex($index);
            if (str_starts_with($name, $requiredPrefix)) {
                $hasRequiredFolder = true;
                break;
            }
        }

        $zip->close();

        if (! $hasContentTypes || ! $hasRequiredFolder) {
            throw ValidationException::withMessages(['file' => $message]);
        }
    }
}
