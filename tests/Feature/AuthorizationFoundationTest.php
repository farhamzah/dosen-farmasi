<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\Document;
use App\Models\PortfolioActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorizationFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_cannot_view_other_lecturer_activity(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'A', 'role' => 'dosen', 'is_active' => true]);
        $activity = PortfolioActivity::query()->create([
            'lecturer_core_id' => '20',
            'activity_type' => 'Penelitian',
            'title' => 'Aktivitas B',
        ]);

        $this->assertFalse($user->can('view', $activity));
    }

    public function test_admin_can_view_other_lecturer_activity(): void
    {
        $admin = AppUser::query()->create(['core_user_id' => '9', 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);
        $activity = PortfolioActivity::query()->create([
            'lecturer_core_id' => '20',
            'activity_type' => 'Penelitian',
            'title' => 'Aktivitas B',
        ]);

        $this->assertTrue($admin->can('view', $activity));
    }

    public function test_dosen_cannot_download_other_lecturer_document(): void
    {
        Storage::fake('shared_private');
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'A', 'role' => 'dosen', 'is_active' => true]);
        $document = Document::query()->create([
            'lecturer_core_id' => '20',
            'document_type' => 'sertifikat',
            'title' => 'Dokumen',
            'disk' => 'shared_private',
            'path' => 'x/file.pdf',
            'original_filename' => 'file.pdf',
            'stored_filename' => 'stored.pdf',
            'extension' => 'pdf',
            'sha256_checksum' => str_repeat('a', 64),
        ]);

        $this->actingAs($user)->get(route('documents.download', $document))->assertForbidden();
    }
}
