<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\Document;
use App\Models\PortfolioActivity;
use App\Services\TridharmaExportService;
use App\Support\PortfolioUi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiUsabilityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_exports_do_not_mislabel_private_notes_or_source_ids(): void
    {
        $activity = new PortfolioActivity([
            'title' => 'Contoh kegiatan', 'activity_type' => 'penelitian',
            'source_entity' => 'TECHNICAL-SOURCE-ID', 'personal_notes' => 'PRIVATE-NOTE',
            'academic_year' => '2026/2027', 'verification_status' => 'DRAFT',
        ]);
        foreach (['pendidikan', 'publikasi', 'hki', 'buku'] as $kind) {
            ob_start();
            try {
                app(TridharmaExportService::class)->stream(collect([$activity]), $kind);
                $output = ob_get_contents();
            } finally {
                ob_end_clean();
            }
            $this->assertStringNotContainsString('TECHNICAL-SOURCE-ID', $output);
            $this->assertStringNotContainsString('PRIVATE-NOTE', $output);
        }
    }

    public function test_filter_chips_preserve_search_and_date_values(): void
    {
        $this->assertSame('2026-09-24', PortfolioUi::filterValue('date_from', '2026-09-24'));
        $this->assertSame('uji-klinik', PortfolioUi::filterValue('q', 'uji-klinik'));
        $this->assertSame('Publik', PortfolioUi::filterValue('visibility', 'PUBLIC'));
    }

    public function test_cv_templates_have_document_layout_and_return_to_template_picker(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        foreach (['akademik', 'impact', 'editorial'] as $template) {
            $this->actingAs($user)->get(route('profile.preview', ['template' => $template]))
                ->assertOk()->assertSee('df-cv-'.$template)->assertSee(route('profile.show').'#profil-publik')
                ->assertSee('Cetak / Simpan PDF');
        }
    }

    public function test_document_counts_cover_all_pages_but_only_the_current_lecturer(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        foreach (range(1, 13) as $index) {
            Document::query()->create([
                'lecturer_core_id' => $index === 13 ? '20' : '10',
                'document_type' => 'sertifikat', 'title' => 'Dokumen '.$index,
                'disk' => 'shared_private', 'path' => 'qa/file.pdf',
                'original_filename' => 'file.pdf', 'stored_filename' => 'file.pdf',
                'extension' => 'pdf', 'sha256_checksum' => str_repeat('a', 64),
                'verification_status' => $index <= 11 ? 'PENDING' : 'VERIFIED',
            ]);
        }
        $this->actingAs($user)->get(route('dosen.documents.index'))
            ->assertOk()->assertViewHas('documentCount', 12)->assertViewHas('pendingCount', 11)
            ->assertViewHas('verifiedCount', 1)->assertDontSee('Dokumen 13');
        $this->get(route('dosen.documents.index', ['q' => 'Dokumen 12']))
            ->assertOk()->assertViewHas('documents', fn ($documents) => $documents->total() === 1)
            ->assertSee('Dokumen 12')->assertDontSee('Dokumen 13');
    }

    public function test_profile_validation_is_readable_and_retains_visibility_without_creating_data(): void
    {
        app()->setLocale('id');
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $this->actingAs($user)->from(route('profile.show'))->post(route('profile.educations.store'), [
            '_form_key' => '/profil/pendidikan:post', 'level' => 'S2', 'institution_name' => 'Universitas Demo',
            'start_year' => '2020', 'end_year' => '1800', 'graduation_status' => 'LULUS', 'visibility' => 'PUBLIC',
        ])->assertRedirect(route('profile.show'))->assertSessionHasErrors(['end_year' => 'Tahun lulus minimal 1900.'])
            ->assertSessionHasInput('visibility', 'PUBLIC')->assertSessionHasInput('institution_name', 'Universitas Demo');
        $this->assertDatabaseCount('lecturer_educations', 0);
        $this->get(route('profile.show'))->assertOk()->assertSee('form-recovery')->assertSee('Tahun lulus minimal 1900.');
    }
}
