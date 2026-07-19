<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\PortfolioActivity;
use App\Models\PortfolioParticipant;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantTagM2Test extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_participant_and_attach_tag(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $activity = PortfolioActivity::query()->create(['lecturer_core_id' => '10', 'activity_type' => 'Pengabdian', 'title' => 'Kegiatan', 'verification_status' => 'DRAFT']);
        $tag = Tag::query()->create(['name' => 'Publikasi', 'normalized_name' => Tag::normalize('publikasi')]);

        $this->actingAs($user)->post(route('dosen.portfolio.participants.store', $activity), [
            'participant_type' => 'EXTERNAL_PERSON',
            'external_name' => 'Mitra A',
            'role' => 'MITRA',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('dosen.portfolio.tags.attach', $activity), [
            'tag_id' => $tag->id,
        ])->assertRedirect();

        $this->assertSame(1, PortfolioParticipant::query()->count());
        $this->assertTrue($activity->tags()->whereKey($tag->id)->exists());
    }

    public function test_other_dosen_cannot_add_participant(): void
    {
        $other = AppUser::query()->create(['core_user_id' => '2', 'core_lecturer_id' => '99', 'name' => 'Other', 'role' => 'dosen', 'is_active' => true]);
        $activity = PortfolioActivity::query()->create(['lecturer_core_id' => '10', 'activity_type' => 'Pengabdian', 'title' => 'Kegiatan', 'verification_status' => 'DRAFT']);

        $this->actingAs($other)->post(route('dosen.portfolio.participants.store', $activity), [
            'participant_type' => 'EXTERNAL_PERSON',
            'external_name' => 'Mitra A',
            'role' => 'MITRA',
        ])->assertForbidden();
    }
}
