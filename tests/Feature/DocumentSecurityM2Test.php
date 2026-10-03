<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\PortfolioActivity;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentSecurityM2Test extends TestCase
{
    use RefreshDatabase;

    public function test_missing_official_file_returns_not_found_instead_of_server_error(): void
    {
        Storage::fake('shared_private');
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $document = Document::query()->create([
            'lecturer_core_id' => '10',
            'document_type' => 'SURAT_TUGAS',
            'title' => 'Surat dari TU',
            'disk' => 'shared_private',
            'path' => 'tu/missing.pdf',
            'original_filename' => 'surat.pdf',
            'stored_filename' => 'missing.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 10,
            'sha256_checksum' => str_repeat('a', 64),
            'verification_status' => 'OFFICIAL',
            'visibility' => 'INTERNAL',
            'source_app' => 'tu-farmasi',
            'source_record_id' => 'letter-missing',
        ]);

        $this->actingAs($user)->get(route('documents.download', $document))->assertNotFound();
    }

    public function test_document_upload_creates_private_metadata_version_and_activity_link(): void
    {
        Storage::fake('shared_private');
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $activity = PortfolioActivity::query()->create(['lecturer_core_id' => '10', 'activity_type' => 'Penelitian', 'title' => 'Draft', 'verification_status' => 'DRAFT']);

        $this->actingAs($user)->post(route('dosen.documents.store'), [
            'portfolio_activity_id' => $activity->id,
            'document_type' => 'SERTIFIKAT',
            'title' => 'Sertifikat',
            'visibility' => 'PRIVATE',
            'file' => UploadedFile::fake()->createWithContent('bukti.pdf', "%PDF-1.4\nvalid"),
        ])->assertRedirect(route('dosen.documents.index'));

        $document = Document::query()->firstOrFail();
        $this->assertNotSame('bukti.pdf', $document->stored_filename);
        $this->assertSame(64, strlen($document->sha256_checksum));
        $this->assertSame(1, DocumentVersion::query()->where('document_id', $document->id)->count());
        $this->assertTrue($activity->documents()->whereKey($document->id)->exists());
        Storage::disk('shared_private')->assertExists($document->path);
    }

    public function test_rector_letter_upload_from_portfolio_keeps_private_link_and_returns_to_activity(): void
    {
        Storage::fake('shared_private');
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $activity = PortfolioActivity::query()->create([
            'lecturer_core_id' => '10',
            'activity_type' => 'Kepanitiaan',
            'title' => 'Panitia Dies Natalis',
            'verification_status' => 'DRAFT',
        ]);

        $this->actingAs($user)->get(route('dosen.portfolio.show', $activity))
            ->assertOk()
            ->assertSee('Unggah SK / Surat');
        $this->actingAs($user)->get(route('dosen.documents.create', [
            'portfolio_activity_id' => $activity->id,
            'document_type' => 'SURAT_KEPUTUSAN',
            'return_to_activity' => 1,
        ]))->assertOk()
            ->assertSee('value="'.$activity->id.'" selected', false)
            ->assertSee('value="SURAT_KEPUTUSAN" selected', false)
            ->assertSee('name="return_to_activity" value="1"', false);

        $this->actingAs($user)->post(route('dosen.documents.store'), [
            'portfolio_activity_id' => $activity->id,
            'return_to_activity' => 1,
            'document_type' => 'SURAT_KEPUTUSAN',
            'title' => 'SK Rektor Panitia Dies Natalis',
            'document_number' => '184/R/SK/2026',
            'document_date' => '2026-09-21',
            'issuer' => 'Rektor Universitas Buana Perjuangan Karawang',
            'visibility' => 'PRIVATE',
            'file' => UploadedFile::fake()->createWithContent('sk-rektor.pdf', "%PDF-1.4\nvalid"),
        ])->assertRedirect(route('dosen.portfolio.show', $activity));

        $document = Document::query()->firstOrFail();
        $this->assertSame('PRIVATE', $document->visibility);
        $this->assertSame('SURAT_KEPUTUSAN', $document->document_type);
        $this->assertTrue($activity->documents()->whereKey($document->id)->exists());
        Storage::disk('shared_private')->assertExists($document->path);
    }

    public function test_document_create_cannot_preselect_another_lecturers_activity(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $otherActivity = PortfolioActivity::query()->create([
            'lecturer_core_id' => '20',
            'activity_type' => 'Kepanitiaan',
            'title' => 'Kegiatan Dosen Lain',
            'verification_status' => 'DRAFT',
        ]);

        $this->actingAs($user)->get(route('dosen.documents.create', ['portfolio_activity_id' => $otherActivity->id]))
            ->assertOk()
            ->assertDontSee('Kegiatan Dosen Lain')
            ->assertDontSee('value="'.$otherActivity->id.'" selected', false);

        Storage::fake('shared_private');
        $this->actingAs($user)->post(route('dosen.documents.store'), [
            'portfolio_activity_id' => $otherActivity->id,
            'document_type' => 'SURAT_KEPUTUSAN',
            'title' => 'SK',
            'file' => UploadedFile::fake()->createWithContent('sk.pdf', "%PDF-1.4\nvalid"),
        ])->assertForbidden();
        $this->assertDatabaseCount('documents', 0);
    }

    public function test_document_upload_database_failure_removes_file(): void
    {
        Storage::fake('shared_private');
        $this->mock(AuditLogger::class, function ($mock): void {
            $mock->shouldReceive('record')->andThrow(new \RuntimeException('db down'));
        });
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($user)->post(route('dosen.documents.store'), [
                'document_type' => 'SERTIFIKAT',
                'title' => 'Sertifikat',
                'visibility' => 'PRIVATE',
                'file' => UploadedFile::fake()->createWithContent('bukti.pdf', "%PDF-1.4\nvalid"),
            ]);

            $this->fail('Expected upload transaction failure.');
        } catch (\RuntimeException) {
            //
        }

        $this->assertCount(0, Storage::disk('shared_private')->allFiles());
    }

    public function test_pdf_extension_with_wrong_contents_is_rejected(): void
    {
        Storage::fake('shared_private');
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);

        $this->actingAs($user)->post(route('dosen.documents.store'), [
            'document_type' => 'SERTIFIKAT',
            'title' => 'Sertifikat',
            'visibility' => 'PRIVATE',
            'file' => UploadedFile::fake()->createWithContent('bukti.pdf', 'not a pdf'),
        ])->assertSessionHasErrors('file');

        $this->assertSame(0, Document::query()->count());
        $this->assertCount(0, Storage::disk('shared_private')->allFiles());
    }

    public function test_executable_renamed_as_pdf_is_rejected(): void
    {
        Storage::fake('shared_private');
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);

        $this->actingAs($user)->post(route('dosen.documents.store'), [
            'document_type' => 'SERTIFIKAT',
            'title' => 'Sertifikat',
            'visibility' => 'PRIVATE',
            'file' => UploadedFile::fake()->createWithContent('malware.pdf', "MZ\x90\x00binary"),
        ])->assertSessionHasErrors('file');

        $this->assertSame(0, Document::query()->count());
        $this->assertCount(0, Storage::disk('shared_private')->allFiles());
    }

    public function test_plain_zip_renamed_as_docx_is_rejected(): void
    {
        Storage::fake('shared_private');
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);

        $this->actingAs($user)->post(route('dosen.documents.store'), [
            'document_type' => 'SERTIFIKAT',
            'title' => 'Sertifikat',
            'visibility' => 'PRIVATE',
            'file' => UploadedFile::fake()->createWithContent('arsip.docx', "PK\x03\x04fake zip"),
        ])->assertSessionHasErrors('file');

        $this->assertSame(0, Document::query()->count());
        $this->assertCount(0, Storage::disk('shared_private')->allFiles());
    }

    public function test_official_document_cannot_be_deleted_by_dosen(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $document = Document::query()->create([
            'lecturer_core_id' => '10',
            'document_type' => 'SURAT_TUGAS',
            'title' => 'Resmi',
            'disk' => 'shared_private',
            'path' => 'x/file.pdf',
            'original_filename' => 'file.pdf',
            'stored_filename' => 'stored.pdf',
            'extension' => 'pdf',
            'sha256_checksum' => str_repeat('a', 64),
            'source_app' => 'tu-farmasi',
        ]);

        $this->actingAs($user)->delete(route('dosen.documents.destroy', $document))->assertForbidden();
    }
}
