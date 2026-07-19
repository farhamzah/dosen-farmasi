<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_users', function (Blueprint $table): void {
            $table->id();
            $table->string('core_user_id')->unique();
            $table->string('core_lecturer_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('lecturer_number')->nullable()->index();
            $table->string('nip')->nullable()->index();
            $table->string('nidn')->nullable()->index();
            $table->string('role')->index();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lecturer_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('core_user_id')->nullable()->index();
            $table->string('core_lecturer_id')->unique();
            $table->string('lecturer_number')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('nip')->nullable()->index();
            $table->string('nidn')->nullable()->index();
            $table->string('sister_id_sdm')->nullable()->index();
            $table->string('study_program_id')->nullable()->index();
            $table->string('study_program_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('portfolio_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('portfolio_activities', function (Blueprint $table): void {
            $table->id();
            $table->string('lecturer_core_id')->index();
            $table->foreignId('category_id')->nullable()->constrained('portfolio_categories')->nullOnDelete();
            $table->string('activity_type')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('lecturer_role')->nullable();
            $table->string('academic_year')->nullable()->index();
            $table->string('semester')->nullable()->index();
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable();
            $table->string('institution_name')->nullable();
            $table->string('location')->nullable();
            $table->string('verification_status')->default('DRAFT')->index();
            $table->string('source_type')->default('MANUAL')->index();
            $table->string('source_app')->nullable()->index();
            $table->string('source_entity')->nullable();
            $table->string('source_record_id')->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->string('visibility')->default('PRIVATE')->index();
            $table->string('created_by_core_user_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['source_app', 'source_entity', 'source_record_id', 'lecturer_core_id'], 'portfolio_source_unique');
        });

        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->string('lecturer_core_id')->index();
            $table->string('document_type')->index();
            $table->string('title');
            $table->string('disk');
            $table->string('path', 1000);
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('extension', 20);
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('sha256_checksum', 64)->index();
            $table->string('uploaded_by_core_user_id')->nullable()->index();
            $table->string('source_app')->nullable()->index();
            $table->string('source_record_id')->nullable();
            $table->string('verification_status')->default('PENDING')->index();
            $table->timestamps();
        });

        Schema::create('activity_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('portfolio_activity_id')->constrained('portfolio_activities')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['portfolio_activity_id', 'document_id']);
        });

        Schema::create('inbox_items', function (Blueprint $table): void {
            $table->id();
            $table->string('lecturer_core_id')->index();
            $table->string('type')->index();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->string('priority')->default('NORMAL')->index();
            $table->string('status')->default('UNREAD')->index();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('occurred_at')->nullable()->index();
            $table->string('action_url', 1000)->nullable();
            $table->string('source_app')->nullable()->index();
            $table->string('source_record_id')->nullable();
            $table->timestamps();
            $table->unique(['source_app', 'source_record_id', 'lecturer_core_id', 'type'], 'inbox_source_unique');
        });

        Schema::create('calendar_events', function (Blueprint $table): void {
            $table->id();
            $table->string('lecturer_core_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type')->index();
            $table->string('status')->default('SCHEDULED')->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('source_app')->nullable()->index();
            $table->string('source_record_id')->nullable();
            $table->timestamps();
            $table->unique(['source_app', 'source_record_id', 'lecturer_core_id', 'event_type'], 'calendar_source_unique');
        });

        Schema::create('integration_clients', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('app_code')->unique();
            $table->string('token_hash')->nullable();
            $table->json('abilities')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('integration_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('event_type')->index();
            $table->unsignedInteger('event_version')->default(1);
            $table->string('source_app')->index();
            $table->string('source_record_id')->nullable()->index();
            $table->string('lecturer_core_id')->nullable()->index();
            $table->json('payload')->nullable();
            $table->string('payload_hash', 64)->index();
            $table->string('status')->default('RECEIVED')->index();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->string('error_code')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('actor_core_user_id')->nullable()->index();
            $table->string('actor_role')->nullable()->index();
            $table->string('action')->index();
            $table->string('subject_type')->nullable()->index();
            $table->string('subject_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->json('safe_metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('application_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('application_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('integration_events');
        Schema::dropIfExists('integration_clients');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('inbox_items');
        Schema::dropIfExists('activity_documents');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('portfolio_activities');
        Schema::dropIfExists('portfolio_categories');
        Schema::dropIfExists('lecturer_snapshots');
        Schema::dropIfExists('app_users');
    }
};
