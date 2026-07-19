<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\CalendarEvent;
use App\Models\Document;
use App\Models\LecturerCertification;
use App\Models\LecturerEducation;
use App\Models\LecturerExpertiseArea;
use App\Models\LecturerExternalIdentifier;
use App\Models\LecturerFunctionalPosition;
use App\Models\PortfolioActivity;
use App\Models\PortfolioCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class M8TridharmaProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_sees_own_tridharma_and_not_other_lecturer_activity(): void
    {
        $this->seed();
        $user = $this->dosen();
        $other = $this->dosen('2', '20', 'Dosen Lain');
        $category = PortfolioCategory::query()->where('slug', 'pendidikan-dan-pengajaran')->firstOrFail();

        PortfolioActivity::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'category_id' => $category->id,
            'activity_type' => 'perkuliahan',
            'title' => 'Mengajar Farmakologi Klinik',
            'verification_status' => 'SYSTEM_VERIFIED',
            'source_type' => 'SYSTEM',
            'source_app' => 'ta-farmasi',
            'visibility' => 'INTERNAL',
        ]);

        PortfolioActivity::query()->create([
            'lecturer_core_id' => $other->core_lecturer_id,
            'category_id' => $category->id,
            'activity_type' => 'perkuliahan',
            'title' => 'Aktivitas Dosen Lain',
            'verification_status' => 'SYSTEM_VERIFIED',
            'source_type' => 'SYSTEM',
            'source_app' => 'kp-farmasi',
            'visibility' => 'INTERNAL',
        ]);

        $this->actingAs($user)
            ->get(route('tridharma.domain', 'pendidikan'))
            ->assertOk()
            ->assertSee('Pendidikan dan Pengajaran')
            ->assertSee('Mengajar Farmakologi Klinik')
            ->assertSee('TA Farmasi')
            ->assertDontSee('ta-farmasi')
            ->assertDontSee('Aktivitas Dosen Lain');
    }

    public function test_tridharma_domain_does_not_fall_back_to_generic_portfolio_list(): void
    {
        $this->seed();
        $user = $this->dosen();
        $education = PortfolioCategory::query()->where('slug', 'pendidikan-dan-pengajaran')->firstOrFail();
        $research = PortfolioCategory::query()->where('slug', 'penelitian-dan-pengembangan')->firstOrFail();

        PortfolioActivity::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'category_id' => $education->id,
            'activity_type' => 'praktikum',
            'title' => 'Praktikum Farmasetika',
            'verification_status' => 'DRAFT',
            'source_type' => 'MANUAL',
            'visibility' => 'PRIVATE',
        ]);

        PortfolioActivity::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'category_id' => $research->id,
            'activity_type' => 'publikasi-jurnal',
            'title' => 'Publikasi Jurnal Fitokimia',
            'verification_status' => 'DRAFT',
            'source_type' => 'MANUAL',
            'visibility' => 'PRIVATE',
        ]);

        $this->actingAs($user)
            ->get(route('tridharma.domain', 'pendidikan'))
            ->assertOk()
            ->assertSee('Praktikum Farmasetika')
            ->assertDontSee('Publikasi Jurnal Fitokimia');
    }

    public function test_education_crud_defaults_basic_school_private_and_keeps_document_private(): void
    {
        $user = $this->dosen();
        $document = Document::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'document_type' => 'IJAZAH',
            'title' => 'Ijazah SMA',
            'disk' => 'local',
            'path' => 'private/ijazah.pdf',
            'original_filename' => 'ijazah.pdf',
            'stored_filename' => 'ijazah.pdf',
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'sha256_checksum' => str_repeat('a', 64),
            'visibility' => 'PUBLIC',
        ]);

        $this->actingAs($user)
            ->post(route('profile.educations.store'), [
                'level' => 'SMA',
                'institution_name' => 'SMA Negeri Farmasi',
                'graduation_status' => 'LULUS',
                'document_id' => $document->id,
            ])
            ->assertRedirect(route('profile.show'));

        $education = LecturerEducation::query()->firstOrFail();
        $this->assertSame('PRIVATE', $education->visibility);
        $this->assertSame('PRIVATE', $document->fresh()->visibility);

        $this->actingAs($user)
            ->put(route('profile.educations.update', $education), [
                'level' => 'S1',
                'institution_name' => 'Universitas Farmasi',
                'study_program' => 'Farmasi',
                'graduation_status' => 'LULUS',
                'visibility' => 'PUBLIC',
            ])
            ->assertRedirect(route('profile.show'));

        $this->assertSame('PUBLIC', $education->fresh()->visibility);

        $this->actingAs($user)
            ->delete(route('profile.educations.destroy', $education))
            ->assertRedirect(route('profile.show'));
        $this->assertSoftDeleted($education);
    }

    public function test_other_lecturer_education_is_denied(): void
    {
        $owner = $this->dosen();
        $other = $this->dosen('2', '20', 'Dosen Lain');
        $education = LecturerEducation::query()->create([
            'lecturer_core_id' => $owner->core_lecturer_id,
            'level' => 'S2',
            'institution_name' => 'Universitas Pemilik',
            'graduation_status' => 'LULUS',
            'verification_status' => 'DRAFT',
            'visibility' => 'INTERNAL',
            'source_type' => 'MANUAL',
        ]);

        $this->actingAs($other)
            ->put(route('profile.educations.update', $education), [
                'level' => 'S2',
                'institution_name' => 'Tidak Boleh',
                'graduation_status' => 'LULUS',
            ])
            ->assertForbidden();
    }

    public function test_active_position_history_visibility_and_public_profile_safety(): void
    {
        $user = $this->dosen();

        LecturerFunctionalPosition::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'position_name' => 'Lektor',
            'is_active' => true,
            'verification_status' => 'VERIFIED',
            'source_type' => 'MANUAL',
        ]);

        LecturerEducation::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'level' => 'S3',
            'institution_name' => 'Universitas Publik',
            'study_program' => 'Farmasi',
            'graduation_status' => 'LULUS',
            'visibility' => 'PUBLIC',
            'verification_status' => 'VERIFIED',
            'source_type' => 'MANUAL',
        ]);

        LecturerEducation::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'level' => 'SMA',
            'institution_name' => 'Sekolah Private',
            'graduation_status' => 'LULUS',
            'visibility' => 'PRIVATE',
            'verification_status' => 'VERIFIED',
            'source_type' => 'MANUAL',
        ]);

        $this->actingAs($user)
            ->post(route('profile.visibility.update'), [
                'public_profile_enabled' => '1',
                'section_visibility' => ['education' => 'PUBLIC'],
            ])
            ->assertRedirect(route('profile.show'));

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Lektor')
            ->assertSee('Aktif')
            ->assertDontSee('source_record_id');

        $this->get(route('profile.public', $user->core_lecturer_id))
            ->assertOk()
            ->assertSee('Universitas Publik')
            ->assertDontSee('Sekolah Private')
            ->assertDontSee($user->nip ?? 'NIP-SECRET');
    }

    public function test_admin_can_verify_education_but_dosen_cannot_verify_self(): void
    {
        $dosen = $this->dosen();
        $admin = AppUser::query()->create(['core_user_id' => '9', 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);
        $education = LecturerEducation::query()->create([
            'lecturer_core_id' => $dosen->core_lecturer_id,
            'level' => 'S2',
            'institution_name' => 'Universitas Verifikasi',
            'graduation_status' => 'LULUS',
            'verification_status' => 'DRAFT',
            'visibility' => 'INTERNAL',
            'source_type' => 'MANUAL',
        ]);

        $this->actingAs($dosen)
            ->post(route('admin.lecturer-educations.verify', $education))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.lecturer-educations.verify', $education))
            ->assertRedirect();

        $this->assertSame('VERIFIED', $education->fresh()->verification_status);
    }

    public function test_unsafe_identifier_url_is_rejected_and_technical_source_id_not_rendered(): void
    {
        $user = $this->dosen();

        $this->actingAs($user)
            ->post(route('profile.identifiers.store'), [
                'identifier_type' => 'ORCID',
                'identifier_value' => '0000-0000-0000-0000',
                'profile_url' => 'javascript:alert(1)',
                'visibility' => 'PUBLIC',
            ])
            ->assertSessionHasErrors('profile_url');

        LecturerExternalIdentifier::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'identifier_type' => 'SINTA',
            'identifier_value' => '12345',
            'source_record_id' => 'SRC-TECHNICAL-999',
            'verification_status' => 'VERIFIED',
            'visibility' => 'INTERNAL',
            'source_type' => 'SYSTEM',
        ]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('SINTA')
            ->assertDontSee('SRC-TECHNICAL-999');
    }

    public function test_admin_academic_resources_load(): void
    {
        $admin = AppUser::query()->create(['core_user_id' => '9', 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('filament.admin.resources.lecturer-educations.index'))
            ->assertOk()
            ->assertSee('Pendidikan Dosen');

        $this->actingAs($admin)
            ->get(route('filament.admin.resources.lecturer-external-identifiers.index'))
            ->assertOk()
            ->assertSee('Identitas Ilmiah');
    }

    public function test_premium_shell_navigation_and_mobile_bottom_labels_render_for_dosen(): void
    {
        $user = $this->dosen();

        $this->actingAs($user)
            ->get(route('dosen.dashboard'))
            ->assertOk()
            ->assertSee('Ruang Akademik')
            ->assertSee('Ruang Kerja')
            ->assertSee('Profil Akademik')
            ->assertSee('Beranda')
            ->assertSee('Tridharma')
            ->assertDontSee('Ruang Kontrol Admin');
    }

    public function test_ui_demo_seed_command_populates_dosen_workspace(): void
    {
        $user = $this->dosen();

        $this->artisan('dosen:seed-ui-demo', ['--clear' => true])->assertSuccessful();

        $this->assertDatabaseHas('portfolio_activities', [
            'lecturer_core_id' => $user->core_lecturer_id,
            'source_app' => 'm8-ui-demo',
        ]);
        $this->assertDatabaseHas('documents', [
            'lecturer_core_id' => $user->core_lecturer_id,
            'source_app' => 'm8-ui-demo',
        ]);
        $this->assertDatabaseHas('inbox_items', [
            'lecturer_core_id' => $user->core_lecturer_id,
            'source_app' => 'm8-ui-demo',
        ]);
        $this->assertDatabaseHas('calendar_events', [
            'lecturer_core_id' => $user->core_lecturer_id,
            'source_app' => 'm8-ui-demo',
        ]);
    }

    public function test_tridharma_premium_empty_state_and_filter_structure_render(): void
    {
        $this->seed();
        $user = $this->dosen();

        $this->actingAs($user)
            ->get(route('tridharma.index'))
            ->assertOk()
            ->assertSee('Perjalanan Tridharma Semester Ini')
            ->assertSee('Kelengkapan Tridharma')
            ->assertSee('Belum ada kegiatan pada periode ini')
            ->assertSee('Filter')
            ->assertSee('Sumber Otomatis');
    }

    public function test_profile_premium_populated_sections_render_without_technical_id_leakage(): void
    {
        $user = $this->dosen();

        LecturerFunctionalPosition::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'position_name' => 'Lektor Kepala',
            'credit_score' => '400',
            'is_active' => true,
            'verification_status' => 'VERIFIED',
            'source_type' => 'MANUAL',
        ]);

        LecturerEducation::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'level' => 'S3',
            'institution_name' => 'Universitas Riset Farmasi',
            'study_program' => 'Ilmu Farmasi',
            'degree' => 'Dr.',
            'graduation_status' => 'LULUS',
            'visibility' => 'INTERNAL',
            'verification_status' => 'VERIFIED',
            'source_type' => 'MANUAL',
            'source_record_id' => 'SECRET-EDU-001',
        ]);

        LecturerExpertiseArea::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'primary_expertise' => 'Farmakologi Klinik',
            'specializations' => ['Farmakovigilans'],
            'research_topics' => ['Keamanan Obat'],
            'visibility' => 'INTERNAL',
            'verification_status' => 'DRAFT',
            'source_type' => 'MANUAL',
        ]);

        LecturerCertification::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'category' => 'profesi',
            'name' => 'Sertifikasi Kompetensi Dosen Farmasi',
            'issuer' => 'Lembaga Sertifikasi',
            'status' => 'AKTIF',
            'visibility' => 'INTERNAL',
            'verification_status' => 'DRAFT',
            'source_type' => 'MANUAL',
        ]);

        LecturerExternalIdentifier::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'identifier_type' => 'ORCID',
            'identifier_value' => '0000-0002-0000-0000',
            'verification_status' => 'VERIFIED',
            'visibility' => 'PUBLIC',
            'source_type' => 'MANUAL',
        ]);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Profil Akademik')
            ->assertSee('Riwayat Pendidikan')
            ->assertSee('Lektor Kepala')
            ->assertSee('Bidang Kepakaran')
            ->assertSee('Farmakologi Klinik')
            ->assertSee('Identitas Ilmiah')
            ->assertSee('ORCID')
            ->assertSee('Sertifikasi Kompetensi Dosen Farmasi')
            ->assertDontSee('SECRET-EDU-001');
    }

    public function test_admin_terminology_is_renamed_and_dosen_cannot_open_admin_panel(): void
    {
        $admin = AppUser::query()->create(['core_user_id' => '9', 'name' => 'Admin', 'role' => 'admin', 'is_active' => true]);
        $dosen = $this->dosen();

        $this->actingAs($admin)
            ->get(route('filament.admin.resources.integration-clients.index'))
            ->assertOk()
            ->assertSee('Aplikasi Terhubung')
            ->assertDontSee('Klien Integrasi');

        $this->actingAs($admin)
            ->get(route('filament.admin.resources.integration-failures.index'))
            ->assertOk()
            ->assertSee('Kegagalan Sinkronisasi');

        $this->actingAs($dosen)
            ->get(route('filament.admin.resources.integration-clients.index'))
            ->assertForbidden();
    }

    public function test_m8_table_sorting_grouping_view_and_export_are_scoped_and_safe(): void
    {
        $this->seed();
        $user = $this->dosen();
        $other = $this->dosen('2', '20', 'Dosen Lain');
        $category = PortfolioCategory::query()->where('slug', 'penelitian-dan-pengembangan')->firstOrFail();

        $old = PortfolioActivity::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'category_id' => $category->id,
            'activity_type' => 'penelitian',
            'title' => 'Analisis Fitokimia Lama',
            'lecturer_role' => 'Ketua Peneliti',
            'academic_year' => '2025/2026',
            'start_date' => '2025-02-10',
            'verification_status' => 'DRAFT',
            'source_type' => 'MANUAL',
            'visibility' => 'INTERNAL',
        ]);

        $new = PortfolioActivity::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'category_id' => $category->id,
            'activity_type' => 'publikasi-jurnal',
            'title' => 'Publikasi Terbaru Farmakologi',
            'lecturer_role' => 'Penulis Pertama',
            'academic_year' => '2026/2027',
            'start_date' => '2026-07-18',
            'verification_status' => 'SYSTEM_VERIFIED',
            'source_type' => 'SYSTEM',
            'source_app' => 'ta-farmasi',
            'source_record_id' => 'SECRET-SOURCE-ID',
            'visibility' => 'INTERNAL',
        ]);

        PortfolioActivity::query()->create([
            'lecturer_core_id' => $other->core_lecturer_id,
            'category_id' => $category->id,
            'activity_type' => 'penelitian',
            'title' => 'Aktivitas Milik Orang Lain',
            'start_date' => '2027-01-01',
            'verification_status' => 'SYSTEM_VERIFIED',
            'source_type' => 'SYSTEM',
            'source_app' => 'kp-farmasi',
            'visibility' => 'INTERNAL',
        ]);

        $response = $this->actingAs($user)->get(route('tridharma.domain', 'penelitian'));
        $response->assertOk()
            ->assertSee('Tabel')
            ->assertSee('Kartu')
            ->assertSee('Publikasi Terbaru Farmakologi')
            ->assertSee('Terverifikasi Sistem')
            ->assertSee('TA Farmasi')
            ->assertSeeInOrder([$new->title, $old->title])
            ->assertDontSee('SECRET-SOURCE-ID')
            ->assertDontSee('ta-farmasi')
            ->assertDontSee('Aktivitas Milik Orang Lain');

        $this->actingAs($user)
            ->get(route('tridharma.domain', ['penelitian', 'sort' => 'date', 'direction' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder([$old->title, $new->title]);

        $this->actingAs($user)
            ->get(route('tridharma.domain', ['penelitian', 'sort' => 'source_record_id', 'direction' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder([$new->title, $old->title]);

        $this->actingAs($user)
            ->get(route('tridharma.domain', ['penelitian', 'date_from' => '2026-01-01', 'date_to' => '2026-12-31']))
            ->assertOk()
            ->assertSee($new->title)
            ->assertDontSee($old->title);

        $this->actingAs($user)
            ->get(route('tridharma.domain', ['penelitian', 'group' => 'month']))
            ->assertOk()
            ->assertSee('Juli 2026');

        $this->actingAs($user)
            ->get(route('dosen.portfolio.index', ['view' => 'card']))
            ->assertOk()
            ->assertSee('Kartu')
            ->assertSee($new->title);

        $csv = $this->actingAs($user)
            ->get(route('dosen.portfolio.export', ['date_from' => '2026-01-01', 'date_to' => '2026-12-31']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Publikasi Terbaru Farmakologi', $csv);
        $this->assertStringNotContainsString('Analisis Fitokimia Lama', $csv);
        $this->assertStringNotContainsString('SECRET-SOURCE-ID', $csv);
        $this->assertStringNotContainsString('ta-farmasi', $csv);
    }

    public function test_m8_research_subdomain_tables_and_exports_use_academic_columns(): void
    {
        $this->seed();
        $user = $this->dosen();
        $category = PortfolioCategory::query()->where('slug', 'penelitian-dan-pengembangan')->firstOrFail();

        PortfolioActivity::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'category_id' => $category->id,
            'activity_type' => 'publikasi-jurnal',
            'title' => 'Artikel Stabilitas Sediaan Nanoemulsi',
            'lecturer_role' => 'Penulis Korespondensi',
            'academic_year' => '2026/2027',
            'start_date' => '2026-06-01',
            'institution_name' => 'Jurnal Farmasi Klinik',
            'personal_notes' => 'SINTA 2',
            'source_url' => 'https://doi.org/example',
            'verification_status' => 'SYSTEM_VERIFIED',
            'source_type' => 'SYSTEM',
            'source_app' => 'ta-farmasi',
            'source_record_id' => 'TECH-PUB-SECRET',
            'visibility' => 'INTERNAL',
        ]);

        PortfolioActivity::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'category_id' => $category->id,
            'activity_type' => 'hki-paten',
            'title' => 'HKI Modul Edukasi Obat Rasional',
            'lecturer_role' => 'Farhamzah',
            'academic_year' => '2026',
            'start_date' => '2026-05-01',
            'source_entity' => 'EC00202600001',
            'personal_notes' => '000123',
            'verification_status' => 'SUBMITTED',
            'source_type' => 'MANUAL',
            'visibility' => 'INTERNAL',
        ]);

        PortfolioActivity::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'category_id' => $category->id,
            'activity_type' => 'buku',
            'title' => 'Buku Ajar Farmasi Klinik',
            'lecturer_role' => 'Penulis',
            'academic_year' => '2026',
            'start_date' => '2026-04-01',
            'institution_name' => 'UBP Press',
            'source_entity' => '978-602-0000-00-1',
            'verification_status' => 'ADMIN_VERIFIED',
            'source_type' => 'MANUAL',
            'visibility' => 'INTERNAL',
        ]);

        $this->actingAs($user)
            ->get(route('tridharma.domain', ['penelitian', 'subcategory' => 'publikasi-jurnal']))
            ->assertOk()
            ->assertSee('Jurnal/Prosiding')
            ->assertSee('Penulis')
            ->assertSee('DOI/URL')
            ->assertSee('Indeksasi')
            ->assertSee('Artikel Stabilitas Sediaan Nanoemulsi')
            ->assertDontSee('TECH-PUB-SECRET')
            ->assertDontSee('ta-farmasi');

        $this->actingAs($user)
            ->get(route('tridharma.domain', ['penelitian', 'subcategory' => 'hki-paten']))
            ->assertOk()
            ->assertSee('Nomor Permohonan')
            ->assertSee('Nomor Pencatatan')
            ->assertSee('Pemegang Hak')
            ->assertSee('Dokumen')
            ->assertSee('HKI Modul Edukasi Obat Rasional');

        $this->actingAs($user)
            ->get(route('tridharma.domain', ['penelitian', 'subcategory' => 'buku']))
            ->assertOk()
            ->assertSee('Penerbit')
            ->assertSee('ISBN')
            ->assertSee('Buku Ajar Farmasi Klinik');

        $export = $this->actingAs($user)
            ->get(route('tridharma.domain.export', ['penelitian', 'subcategory' => 'publikasi-jurnal', 'format' => 'xls']))
            ->assertOk()
            ->streamedContent();

        $this->assertStringContainsString('Jurnal/Prosiding', $export);
        $this->assertStringContainsString('Artikel Stabilitas Sediaan Nanoemulsi', $export);
        $this->assertStringNotContainsString('TECH-PUB-SECRET', $export);
        $this->assertStringNotContainsString('ta-farmasi', $export);
    }

    public function test_m8_empty_table_and_indonesian_agenda_labels_render(): void
    {
        $this->seed();
        $user = $this->dosen();

        CalendarEvent::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'title' => 'Rapat Koordinasi Praktikum',
            'event_type' => 'MEETING',
            'status' => 'SCHEDULED',
            'starts_at' => '2026-07-19 09:00:00',
            'ends_at' => '2026-07-19 10:30:00',
            'source_app' => 'm8-ui-demo',
            'source_record_id' => 'agenda-demo',
        ]);

        $this->actingAs($user)
            ->get(route('tridharma.domain', 'pengabdian'))
            ->assertOk()
            ->assertSee('Belum ada kegiatan')
            ->assertSee('Tabel')
            ->assertSee('Kartu');

        $this->actingAs($user)
            ->get(route('dosen.calendar.index'))
            ->assertOk()
            ->assertSee('Rapat')
            ->assertSee('Terjadwal')
            ->assertSee('19 Juli 2026')
            ->assertSee('09.00')
            ->assertSee('10.30 WIB')
            ->assertDontSee('Meeting')
            ->assertDontSee('Scheduled');
    }

    private function dosen(string $coreUserId = '1', string $coreLecturerId = '10', string $name = 'Dosen'): AppUser
    {
        return AppUser::query()->create([
            'core_user_id' => $coreUserId,
            'core_lecturer_id' => $coreLecturerId,
            'name' => $name,
            'email' => str($name)->slug().'@example.test',
            'nip' => 'NIP-SECRET',
            'nidn' => 'NIDN-SECRET',
            'role' => 'dosen',
            'is_active' => true,
        ]);
    }
}
