<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\Document;
use App\Models\PortfolioActivity;
use App\Models\PortfolioCategory;
use App\Models\ProfileVisibilitySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HkiPortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_hki_fields_flow_through_crud_table_export_document_and_public_cv_safely(): void
    {
        $this->seed();
        Storage::fake('shared_private');
        $owner = $this->lecturer('1', '10');
        $other = $this->lecturer('2', '20');
        $research = PortfolioCategory::query()->where('slug', 'penelitian-dan-pengembangan')->firstOrFail();
        $supporting = PortfolioCategory::query()->where('slug', 'penunjang')->firstOrFail();

        $this->actingAs($owner)->get(route('tridharma.domain', ['domain' => 'penelitian', 'subcategory' => 'hki-paten']))
            ->assertOk()
            ->assertSee('Tambah HKI');
        $this->actingAs($owner)->get(route('dosen.portfolio.create', ['template' => 'hki']))
            ->assertOk()
            ->assertSee('Nomor permohonan')
            ->assertSee('Status hukum HKI')
            ->assertSee('name="activity_type" value="hki-paten"', false);

        $this->actingAs($owner)->post(route('dosen.portfolio.store'), [
            'category_id' => $supporting->id,
            'activity_type' => 'hki-paten',
            'title' => 'HKI Modul Edukasi Obat',
            'start_date' => '2026-01-10',
            'end_date' => '2026-07-20',
            'visibility' => 'PRIVATE',
            'hki_type' => 'Hak Cipta',
            'hki_application_number' => 'APP-2026-123',
            'hki_registration_number' => 'REG-2026-456',
            'hki_rights_holder' => 'Universitas Buana Perjuangan Karawang',
            'hki_status' => 'TERCATAT',
        ])->assertRedirect();

        $activity = PortfolioActivity::query()->where('title', 'HKI Modul Edukasi Obat')->firstOrFail();
        $this->assertSame($research->id, $activity->category_id);
        $this->assertSame('Hak Cipta', $activity->hki_type);
        $this->assertSame('REG-2026-456', $activity->hki_registration_number);

        ProfileVisibilitySetting::query()->create(['lecturer_core_id' => '10', 'public_profile_enabled' => true]);
        $this->get(route('profile.public', '10'))
            ->assertOk()
            ->assertDontSee('HKI Modul Edukasi Obat');

        $this->actingAs($owner)->get(route('tridharma.domain', ['domain' => 'penelitian', 'subcategory' => 'hki-paten']))
            ->assertOk()
            ->assertSee('APP-2026-123')
            ->assertSee('REG-2026-456')
            ->assertSee('Universitas Buana Perjuangan Karawang');

        $csv = $this->actingAs($owner)->get(route('tridharma.domain.export', [
            'domain' => 'penelitian', 'subcategory' => 'hki-paten', 'format' => 'csv',
        ]))->assertOk()->streamedContent();
        $this->assertStringContainsString('REG-2026-456', $csv);
        $this->assertStringContainsString('Hak Cipta', $csv);

        $this->actingAs($owner)->get(route('dosen.portfolio.show', $activity))
            ->assertOk()
            ->assertSee('Unggah Bukti HKI')
            ->assertSee('Status hukum HKI')
            ->assertSee('REG-2026-456');
        $this->actingAs($owner)->get(route('dosen.portfolio.edit', $activity))
            ->assertOk()
            ->assertSee('name="hki_registration_number" value="REG-2026-456"', false);
        $this->actingAs($owner)->get(route('dosen.documents.create', [
            'portfolio_activity_id' => $activity->id,
            'document_type' => 'BUKTI_HKI',
        ]))->assertOk()->assertSee('BUKTI_HKI');

        $this->actingAs($other)->get(route('dosen.portfolio.show', $activity))->assertForbidden();
        $this->actingAs($other)->put(route('dosen.portfolio.update', $activity), [
            'activity_type' => 'hki-paten', 'title' => 'Diubah', 'visibility' => 'PUBLIC',
        ])->assertForbidden();

        $this->actingAs($owner)->put(route('dosen.portfolio.update', $activity), [
            'category_id' => $supporting->id,
            'activity_type' => 'hki-paten',
            'title' => 'HKI Modul Edukasi Obat',
            'visibility' => 'PUBLIC',
            'hki_type' => 'Hak Cipta',
            'hki_application_number' => 'APP-2026-123',
            'hki_registration_number' => 'REG-2026-789',
            'hki_rights_holder' => 'Universitas Buana Perjuangan Karawang',
            'hki_status' => 'TERBIT',
        ])->assertRedirect(route('dosen.portfolio.show', $activity));
        $this->assertSame($research->id, $activity->fresh()->category_id);
        $this->assertSame('TERBIT', $activity->fresh()->hki_status);

        $this->actingAs($owner)->post(route('dosen.documents.store'), [
            'portfolio_activity_id' => $activity->id,
            'document_type' => 'BUKTI_HKI',
            'title' => 'Sertifikat HKI',
            'visibility' => 'PRIVATE',
            'file' => UploadedFile::fake()->createWithContent('sertifikat.pdf', "%PDF-1.4\nvalid"),
        ])->assertRedirect();
        $document = Document::query()->where('title', 'Sertifikat HKI')->firstOrFail();
        $this->assertSame('BUKTI_HKI', $document->document_type);
        $this->assertTrue($activity->documents()->whereKey($document->id)->exists());

        $this->get(route('profile.public', '10'))
            ->assertOk()
            ->assertSee('HKI Modul Edukasi Obat')
            ->assertSee('Hak Cipta')
            ->assertDontSee('APP-2026-123')
            ->assertDontSee('REG-2026-789');

        $this->actingAs($owner)->delete(route('dosen.portfolio.destroy', $activity))->assertRedirect();
        $this->assertSoftDeleted($activity);
    }

    public function test_hki_status_is_validated_and_non_hki_does_not_accept_hki_fields(): void
    {
        $this->seed();
        $owner = $this->lecturer('1', '10');

        $this->actingAs($owner)->post(route('dosen.portfolio.store'), [
            'activity_type' => 'hki-paten',
            'title' => 'HKI Tidak Valid',
            'visibility' => 'PRIVATE',
            'hki_status' => 'PALSU',
        ])->assertSessionHasErrors('hki_status');

        $this->actingAs($owner)->post(route('dosen.portfolio.store'), [
            'activity_type' => 'penelitian',
            'title' => 'Penelitian Biasa',
            'visibility' => 'PRIVATE',
            'hki_registration_number' => 'TIDAK-BOLEH-TERCATAT',
        ])->assertRedirect();

        $this->assertNull(PortfolioActivity::query()->where('title', 'Penelitian Biasa')->value('hki_registration_number'));
    }

    private function lecturer(string $coreUserId, string $coreLecturerId): AppUser
    {
        return AppUser::query()->create([
            'core_user_id' => $coreUserId,
            'core_lecturer_id' => $coreLecturerId,
            'name' => 'Dosen '.$coreLecturerId,
            'role' => 'dosen',
            'is_active' => true,
        ]);
    }
}
