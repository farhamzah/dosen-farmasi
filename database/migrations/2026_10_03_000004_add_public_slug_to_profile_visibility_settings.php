<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_visibility_settings', function (Blueprint $table): void {
            $table->string('public_slug', 32)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('profile_visibility_settings', function (Blueprint $table): void {
            $table->dropUnique(['public_slug']);
            $table->dropColumn('public_slug');
        });
    }
};
