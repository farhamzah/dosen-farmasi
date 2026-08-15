<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('app_users', 'photo_url')) {
            Schema::table('app_users', function (Blueprint $table): void {
                $table->string('photo_url', 1000)->nullable()->after('email');
            });
        }

        if (! Schema::hasColumn('lecturer_snapshots', 'photo_url')) {
            Schema::table('lecturer_snapshots', function (Blueprint $table): void {
                $table->string('photo_url', 1000)->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_users', 'photo_url')) {
            Schema::table('app_users', function (Blueprint $table): void {
                $table->dropColumn('photo_url');
            });
        }

        if (Schema::hasColumn('lecturer_snapshots', 'photo_url')) {
            Schema::table('lecturer_snapshots', function (Blueprint $table): void {
                $table->dropColumn('photo_url');
            });
        }
    }
};
