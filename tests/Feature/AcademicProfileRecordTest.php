<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\LecturerCertification;
use App\Models\LecturerExpertiseArea;
use App\Models\LecturerFunctionalPosition;
use App\Models\LecturerStructuralPosition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicProfileRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_manage_expertise_and_certification_visibility_for_cv(): void
    {
        $user = $this->dosen();
        $this->actingAs($user);

        $this->post(route('profile.expertise.store'), [
            'primary_expertise' => 'Farmakologi Klinik',
            'specializations' => "Farmakovigilans\nFarmasi Klinik",
            'research_topics' => 'Keamanan Obat',
            'visibility' => 'PUBLIC',
        ])->assertRedirect(route('profile.show').'#keilmuan');

        $this->post(route('profile.certifications.store'), [
            'category' => 'profesi',
            'name' => 'Sertifikat Apoteker',
            'issuer' => 'Organisasi Profesi',
            'status' => 'AKTIF',
            'visibility' => 'PUBLIC',
        ])->assertRedirect(route('profile.show').'#sertifikasi');

        $expertise = LecturerExpertiseArea::query()->firstOrFail();
        $certification = LecturerCertification::query()->firstOrFail();
        $this->assertSame(['Farmakovigilans', 'Farmasi Klinik'], $expertise->specializations);
        $this->assertSame('ADMIN_VERIFIED', $expertise->verification_status);
        $this->assertSame('MANUAL', $certification->source_type);

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Edit kepakaran dan visibilitas')
            ->assertSee('Edit sertifikasi dan visibilitas')
            ->assertSee('Pilih template CV');

        $this->post(route('profile.visibility.update'), [
            'section_visibility' => ['expertise' => 'PUBLIC'],
        ]);
        $this->get(route('profile.preview', ['template' => 'impact']))
            ->assertOk()
            ->assertSee('Pratinjau pribadi')
            ->assertSee('Farmakologi Klinik')
            ->assertSee('Portofolio Mitra');
        $this->get(route('profile.public', $user->core_lecturer_id))->assertNotFound();

        $this->post(route('profile.visibility.update'), [
            'public_profile_enabled' => '1',
            'section_visibility' => ['expertise' => 'PUBLIC'],
        ]);

        $this->get(route('profile.public', $user->core_lecturer_id))
            ->assertOk()
            ->assertSee('Farmakologi Klinik')
            ->assertSee('Sertifikat Apoteker')
            ->assertSee('CV Akademik')
            ->assertSee('Portofolio Mitra')
            ->assertSee('CV Ringkas');

        $this->put(route('profile.expertise.update', $expertise), [
            'primary_expertise' => 'Farmasi Komunitas',
            'visibility' => 'INTERNAL',
        ])->assertRedirect(route('profile.show').'#keilmuan');

        $this->put(route('profile.certifications.update', $certification), [
            'category' => 'profesi',
            'name' => 'Sertifikat Apoteker',
            'status' => 'AKTIF',
            'visibility' => 'PRIVATE',
        ])->assertRedirect(route('profile.show').'#sertifikasi');

        $this->get(route('profile.public', $user->core_lecturer_id))
            ->assertDontSee('Farmasi Komunitas')
            ->assertDontSee('Sertifikat Apoteker');

        $this->delete(route('profile.expertise.destroy', $expertise))->assertRedirect();
        $this->delete(route('profile.certifications.destroy', $certification))->assertRedirect();
        $this->assertSoftDeleted($expertise);
        $this->assertSoftDeleted($certification);
    }

    public function test_manual_career_records_are_editable_and_only_one_manual_position_is_active(): void
    {
        $user = $this->dosen();
        $this->actingAs($user);

        $this->post(route('profile.functional-positions.store'), [
            'position_name' => 'Asisten Ahli',
            'is_active' => '1',
            'visibility' => 'INTERNAL',
        ])->assertRedirect(route('profile.show').'#karier');

        $this->post(route('profile.functional-positions.store'), [
            'position_name' => 'Lektor',
            'is_active' => '1',
            'visibility' => 'PUBLIC',
        ])->assertRedirect(route('profile.show').'#karier');

        $old = LecturerFunctionalPosition::query()->where('position_name', 'Asisten Ahli')->firstOrFail();
        $current = LecturerFunctionalPosition::query()->where('position_name', 'Lektor')->firstOrFail();
        $this->assertFalse($old->fresh()->is_active);
        $this->assertTrue($current->fresh()->is_active);

        $this->post(route('profile.structural-positions.store'), [
            'position_name' => 'Ketua Program Studi',
            'unit' => 'Farmasi',
            'is_active' => '1',
            'visibility' => 'PUBLIC',
        ])->assertRedirect(route('profile.show').'#karier');

        $structural = LecturerStructuralPosition::query()->firstOrFail();
        $this->put(route('profile.functional-positions.update', $current), [
            'position_name' => 'Lektor Kepala',
            'is_active' => '1',
            'visibility' => 'PUBLIC',
        ])->assertRedirect();
        $this->put(route('profile.structural-positions.update', $structural), [
            'position_name' => 'Sekretaris Program Studi',
            'unit' => 'Farmasi',
            'visibility' => 'PRIVATE',
        ])->assertRedirect();

        $this->assertSame('Lektor Kepala', $current->fresh()->position_name);
        $this->assertSame('Sekretaris Program Studi', $structural->fresh()->position_name);
        $this->assertFalse($structural->fresh()->is_active);

        $this->delete(route('profile.functional-positions.destroy', $current))->assertRedirect();
        $this->delete(route('profile.structural-positions.destroy', $structural))->assertRedirect();
        $this->assertSoftDeleted($current);
        $this->assertSoftDeleted($structural);
    }

    public function test_other_lecturers_and_integrated_records_cannot_be_changed(): void
    {
        $owner = $this->dosen('1', '10');
        $other = $this->dosen('2', '20');

        $expertise = LecturerExpertiseArea::query()->create([
            'lecturer_core_id' => '10', 'primary_expertise' => 'Farmakologi', 'source_type' => 'MANUAL',
        ]);
        $certification = LecturerCertification::query()->create([
            'lecturer_core_id' => '10', 'category' => 'profesi', 'name' => 'Sertifikat', 'source_type' => 'MANUAL',
        ]);
        $functional = LecturerFunctionalPosition::query()->create([
            'lecturer_core_id' => '10', 'position_name' => 'Lektor', 'source_type' => 'SYSTEM',
        ]);
        $structural = LecturerStructuralPosition::query()->create([
            'lecturer_core_id' => '10', 'position_name' => 'Ketua Prodi', 'source_type' => 'SYSTEM',
        ]);

        $this->actingAs($other)
            ->put(route('profile.expertise.update', $expertise), ['primary_expertise' => 'Diubah', 'visibility' => 'PUBLIC'])
            ->assertForbidden();
        $this->delete(route('profile.certifications.destroy', $certification))->assertForbidden();

        $this->actingAs($owner)
            ->put(route('profile.functional-positions.update', $functional), ['position_name' => 'Diubah', 'visibility' => 'PUBLIC'])
            ->assertForbidden();
        $this->delete(route('profile.structural-positions.destroy', $structural))->assertForbidden();
    }

    public function test_public_cv_respects_section_visibility_even_for_public_records(): void
    {
        $user = $this->dosen();
        LecturerExpertiseArea::query()->create([
            'lecturer_core_id' => $user->core_lecturer_id,
            'primary_expertise' => 'Keahlian Tersembunyi',
            'visibility' => 'PUBLIC',
        ]);

        $this->actingAs($user)->post(route('profile.visibility.update'), [
            'public_profile_enabled' => '1',
            'section_visibility' => ['expertise' => 'PRIVATE'],
        ]);

        $this->get(route('profile.public', $user->core_lecturer_id))
            ->assertOk()
            ->assertDontSee('Keahlian Tersembunyi');
    }

    public function test_cv_preview_requires_login_and_only_uses_the_current_lecturer(): void
    {
        $this->get(route('profile.preview'))->assertRedirect(route('login'));

        $owner = $this->dosen('1', '10');
        $other = $this->dosen('2', '20');
        LecturerExpertiseArea::query()->create([
            'lecturer_core_id' => '10', 'primary_expertise' => 'Keahlian Pemilik', 'visibility' => 'PUBLIC',
        ]);
        LecturerExpertiseArea::query()->create([
            'lecturer_core_id' => '20', 'primary_expertise' => 'Keahlian Dosen Lain', 'visibility' => 'PUBLIC',
        ]);

        $this->actingAs($owner)->post(route('profile.visibility.update'), [
            'section_visibility' => ['expertise' => 'PUBLIC'],
        ]);
        $this->actingAs($other)->post(route('profile.visibility.update'), [
            'section_visibility' => ['expertise' => 'PUBLIC'],
        ]);

        $this->actingAs($owner)->get(route('profile.preview'))
            ->assertOk()
            ->assertSee('Keahlian Pemilik')
            ->assertDontSee('Keahlian Dosen Lain');
    }

    private function dosen(string $coreUserId = '1', string $lecturerId = '10'): AppUser
    {
        return AppUser::query()->create([
            'core_user_id' => $coreUserId,
            'core_lecturer_id' => $lecturerId,
            'name' => 'Dosen '.$lecturerId,
            'email' => 'dosen'.$lecturerId.'@example.test',
            'role' => 'dosen',
            'is_active' => true,
        ]);
    }
}
