<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_activities', function (Blueprint $table): void {
            $table->string('student_identifier')->nullable()->after('lecturer_role')->index();
            $table->string('student_name')->nullable()->after('student_identifier');
            $table->json('evidence_links')->nullable()->after('source_url');
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_activities', function (Blueprint $table): void {
            $table->dropIndex(['student_identifier']);
            $table->dropColumn(['student_identifier', 'student_name', 'evidence_links']);
        });
    }
};
