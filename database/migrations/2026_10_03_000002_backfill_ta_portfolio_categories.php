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
            ->where('source_app', 'ta-farmasi')
            ->where('source_entity', 'ta.exam')
            ->where('verification_status', 'SYSTEM_VERIFIED')
            ->whereNull('category_id')
            ->update(['category_id' => $categoryId]);
    }

    public function down(): void
    {
        // Existing activity categories cannot be safely restored to their prior state.
    }
};
