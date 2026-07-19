<?php

namespace Tests\Feature;

use App\Models\AppUser;
use App\Models\PortfolioActivity;
use App\Models\PortfolioCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioM2StartTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_can_create_and_submit_manual_draft(): void
    {
        $user = AppUser::query()->create(['core_user_id' => '1', 'core_lecturer_id' => '10', 'name' => 'Dosen', 'role' => 'dosen', 'is_active' => true]);
        $category = PortfolioCategory::query()->create(['name' => 'Penelitian', 'slug' => 'penelitian']);

        $response = $this->actingAs($user)->post(route('dosen.portfolio.store'), [
            'category_id' => $category->id,
            'activity_type' => 'Penelitian',
            'title' => 'Riset Awal',
            'visibility' => 'PRIVATE',
        ]);

        $activity = PortfolioActivity::query()->firstOrFail();
        $response->assertRedirect(route('dosen.portfolio.show', $activity));
        $this->assertSame('DRAFT', $activity->verification_status);

        $this->actingAs($user)->post(route('dosen.portfolio.submit', $activity))->assertRedirect();
        $this->assertSame('SUBMITTED', $activity->fresh()->verification_status);
    }
}
