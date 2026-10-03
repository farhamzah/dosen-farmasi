<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\LecturerExpertiseArea;
use App\Models\ProfileVisibilitySetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CvShareExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_preview_four_formats_and_share_a_revocable_web_portfolio(): void
    {
        $lecturer = AppUser::query()->create([
            'core_user_id' => '1',
            'core_lecturer_id' => '10',
            'name' => 'Dosen Contoh',
            'email' => 'dosen@example.test',
            'role' => 'dosen',
            'is_active' => true,
        ]);
        LecturerExpertiseArea::query()->create([
            'lecturer_core_id' => '10',
            'primary_expertise' => 'Teknologi Farmasi Publik',
            'visibility' => 'PUBLIC',
        ]);
        LecturerExpertiseArea::query()->create([
            'lecturer_core_id' => '10',
            'primary_expertise' => 'Keahlian Rahasia',
            'visibility' => 'PRIVATE',
        ]);

        $this->get(route('profile.preview', ['template' => 'web']))->assertRedirect(route('login'));
        $this->actingAs($lecturer)->get(route('profile.preview', ['template' => 'web']))
            ->assertOk()
            ->assertSee('Pratinjau pribadi')
            ->assertDontSee('Keahlian Rahasia');

        $this->actingAs($lecturer)->post(route('profile.visibility.update'), [
            'public_profile_enabled' => '1',
            'section_visibility' => ['expertise' => 'PUBLIC'],
        ])->assertRedirect();

        $visibility = ProfileVisibilitySetting::query()->where('lecturer_core_id', '10')->firstOrFail();
        $this->assertNotEmpty($visibility->public_slug);
        $shareUrl = route('profile.share', ['slug' => $visibility->public_slug, 'template' => 'web']);

        $this->actingAs($lecturer)->get(route('profile.show'))
            ->assertOk()
            ->assertSee($shareUrl)
            ->assertSee('Salin tautan')
            ->assertSee('Portofolio Web');

        auth()->logout();
        $this->get($shareUrl)
            ->assertOk()
            ->assertSee('df-cv-web')
            ->assertSee('Teknologi Farmasi Publik')
            ->assertDontSee('Keahlian Rahasia')
            ->assertDontSee('core_lecturer_id');

        foreach (['akademik', 'impact', 'editorial'] as $template) {
            $this->get(route('profile.share', ['slug' => $visibility->public_slug, 'template' => $template]))
                ->assertOk()
                ->assertSee('df-cv-'.$template)
                ->assertSee('Cetak / Simpan PDF');
        }

        $this->actingAs($lecturer)->post(route('profile.visibility.update'), [
            'section_visibility' => ['expertise' => 'PUBLIC'],
        ])->assertRedirect();
        $this->get($shareUrl)->assertNotFound();
    }
}
