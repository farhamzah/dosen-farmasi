<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentM2StartTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_can_upload_private_document_with_generated_name_and_checksum(): void
    {
        Storage::fake('shared_private');
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);

        $this->actingAs($user)->post(route('dosen.documents.store'), [
            'document_type' => 'sertifikat',
            'title' => 'Sertifikat',
            'file' => UploadedFile::fake()->createWithContent('bukti.pdf', "%PDF-1.4\nvalid"),
        ])->assertRedirect(route('dosen.documents.index'));

        $document = Document::query()->firstOrFail();
        $this->assertNotSame('bukti.pdf', $document->stored_filename);
        $this->assertSame('bukti.pdf', $document->original_filename);
        $this->assertNotEmpty($document->sha256_checksum);
        Storage::disk('shared_private')->assertExists($document->path);
    }

    public function test_disallowed_document_extension_is_rejected(): void
    {
        Storage::fake('shared_private');
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);

        $this->actingAs($user)->post(route('dosen.documents.store'), [
            'document_type' => 'lainnya',
            'title' => 'Script',
            'file' => UploadedFile::fake()->create('bad.exe', 1, 'application/octet-stream'),
        ])->assertSessionHasErrors('file');
    }
}
