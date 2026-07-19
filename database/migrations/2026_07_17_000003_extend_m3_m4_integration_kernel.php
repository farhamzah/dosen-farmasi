<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inbox_items', function (Blueprint $table): void {
            $table->uuid('group_id')->nullable()->after('id')->index();
            $table->foreignId('document_id')->nullable()->after('action_url')->constrained('documents')->nullOnDelete();
            $table->string('safe_action_url', 1000)->nullable()->after('action_url');
        });

        Schema::create('inbox_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inbox_item_id')->constrained('inbox_items')->cascadeOnDelete();
            $table->string('lecturer_core_id')->index();
            $table->foreignId('app_user_id')->nullable()->constrained('app_users')->nullOnDelete();
            $table->string('status')->default('UNREAD')->index();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['inbox_item_id', 'lecturer_core_id']);
        });

        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->uuid('group_id')->nullable()->after('id')->index();
            $table->foreignId('inbox_item_id')->nullable()->after('meeting_url')->constrained('inbox_items')->nullOnDelete();
            $table->foreignId('document_id')->nullable()->after('inbox_item_id')->constrained('documents')->nullOnDelete();
            $table->unsignedInteger('source_revision')->default(1)->after('source_record_id');
        });

        Schema::create('calendar_event_attendees', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('calendar_event_id')->constrained('calendar_events')->cascadeOnDelete();
            $table->string('lecturer_core_id')->index();
            $table->foreignId('app_user_id')->nullable()->constrained('app_users')->nullOnDelete();
            $table->string('status')->default('INVITED')->index();
            $table->timestamp('responded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['calendar_event_id', 'lecturer_core_id'], 'calendar_attendee_unique');
        });

        Schema::table('integration_clients', function (Blueprint $table): void {
            $table->string('code')->nullable()->unique()->after('id');
            $table->json('allowed_abilities')->nullable()->after('abilities');
            $table->json('allowed_ip_ranges')->nullable()->after('allowed_abilities');
            $table->timestamp('token_rotated_at')->nullable()->after('last_used_at');
            $table->timestamp('token_revoked_at')->nullable()->after('token_rotated_at');
            $table->json('metadata')->nullable()->after('expires_at');
        });

        Schema::table('integration_events', function (Blueprint $table): void {
            $table->foreignId('integration_client_id')->nullable()->after('id')->constrained('integration_clients')->nullOnDelete();
            $table->unsignedInteger('source_revision')->default(1)->after('source_record_id');
            $table->string('correlation_id')->nullable()->index()->after('source_revision');
            $table->timestamp('occurred_at')->nullable()->after('correlation_id');
            $table->timestamp('received_at')->nullable()->after('occurred_at');
            $table->text('result_summary')->nullable()->after('processed_at');
            $table->string('last_error_code')->nullable()->index()->after('error_message');
            $table->text('last_error_message')->nullable()->after('last_error_code');
            $table->json('related_records')->nullable()->after('last_error_message');
        });

        Schema::create('integration_failures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('integration_event_id')->constrained('integration_events')->cascadeOnDelete();
            $table->string('error_code')->index();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->json('safe_context')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('integration_sync_cursors', function (Blueprint $table): void {
            $table->id();
            $table->string('source_app')->index();
            $table->string('cursor_key');
            $table->string('cursor_value')->nullable();
            $table->string('status')->default('IDLE')->index();
            $table->timestamp('last_successful_sync_at')->nullable();
            $table->timestamp('last_attempted_sync_at')->nullable();
            $table->text('error_summary')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['source_app', 'cursor_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_sync_cursors');
        Schema::dropIfExists('integration_failures');

        Schema::table('integration_events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('integration_client_id');
            $table->dropColumn(['source_revision', 'correlation_id', 'occurred_at', 'received_at', 'result_summary', 'last_error_code', 'last_error_message', 'related_records']);
        });

        Schema::table('integration_clients', function (Blueprint $table): void {
            $table->dropColumn(['code', 'allowed_abilities', 'allowed_ip_ranges', 'token_rotated_at', 'token_revoked_at', 'metadata']);
        });

        Schema::dropIfExists('calendar_event_attendees');

        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('inbox_item_id');
            $table->dropConstrainedForeignId('document_id');
            $table->dropColumn(['group_id', 'source_revision']);
        });

        Schema::dropIfExists('inbox_recipients');

        Schema::table('inbox_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('document_id');
            $table->dropColumn(['group_id', 'safe_action_url']);
        });
    }
};
