<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_activities', function (Blueprint $table): void {
            $table->string('hki_type')->nullable();
            $table->string('hki_application_number')->nullable();
            $table->string('hki_registration_number')->nullable();
            $table->string('hki_rights_holder')->nullable();
            $table->string('hki_status')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_activities', function (Blueprint $table): void {
            $table->dropColumn([
                'hki_type',
                'hki_application_number',
                'hki_registration_number',
                'hki_rights_holder',
                'hki_status',
            ]);
        });
    }
};
