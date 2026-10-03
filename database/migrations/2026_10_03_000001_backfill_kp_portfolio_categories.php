<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryId = DB::table('portfolio_categories')
            ->where('slug', 'pendidikan-dan-pengajaran')
            ->value('id');

        if (! $categoryId) {
            $categoryId = DB::table('portfolio_categories')->insertGetId([
                'slug' => 'pendidikan-dan-pengajaran',
                'name' => 'Pendidikan dan Pengajaran',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('portfolio_activities')
            ->where('source_app', 'kp-farmasi')
            ->where('source_entity', 'kp.exam')
            ->whereNull('category_id')
            ->update(['category_id' => $categoryId]);

        DB::table('portfolio_activities')
            ->where('source_app', 'kp-farmasi')
            ->where('source_entity', 'kp.exam')
            ->where('activity_type', 'UJIAN_KP')
            ->where('lecturer_role', 'like', 'PEMBIMBING%')
            ->update(['activity_type' => 'pembimbing-kp']);

        DB::table('portfolio_activities')
            ->where('source_app', 'kp-farmasi')
            ->where('source_entity', 'kp.exam')
            ->where('activity_type', 'UJIAN_KP')
            ->update(['activity_type' => 'penguji-kp']);
    }

    public function down(): void
    {
        // Historical activity categories cannot be safely restored to their prior null state.
    }
};
