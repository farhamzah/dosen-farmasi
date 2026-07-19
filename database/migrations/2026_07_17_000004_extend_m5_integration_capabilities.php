<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_failures', function (Blueprint $table): void {
            $table->string('failure_category')->default('HANDLER_EXCEPTION')->after('error_code')->index();
            $table->boolean('retryable')->default(true)->after('failure_category')->index();
            $table->timestamp('resolved_at')->nullable()->after('safe_context')->index();
            $table->string('resolved_by_core_user_id')->nullable()->after('resolved_at')->index();
            $table->text('resolution_note')->nullable()->after('resolved_by_core_user_id');
        });

        Schema::table('integration_sync_cursors', function (Blueprint $table): void {
            $table->string('last_source_id')->nullable()->after('cursor_value')->index();
            $table->timestamp('last_updated_at')->nullable()->after('last_source_id')->index();
        });

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('app_user_id')->unique()->constrained('app_users')->cascadeOnDelete();
            $table->boolean('email_enabled')->default(false);
            $table->boolean('email_for_assignments')->default(true);
            $table->boolean('email_for_schedule_changes')->default(true);
            $table->boolean('email_for_documents')->default(true);
            $table->string('digest_preference')->default('none');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');

        Schema::table('integration_sync_cursors', function (Blueprint $table): void {
            $table->dropColumn(['last_source_id', 'last_updated_at']);
        });

        Schema::table('integration_failures', function (Blueprint $table): void {
            $table->dropColumn(['failure_category', 'retryable', 'resolved_at', 'resolved_by_core_user_id', 'resolution_note']);
        });
    }
};
