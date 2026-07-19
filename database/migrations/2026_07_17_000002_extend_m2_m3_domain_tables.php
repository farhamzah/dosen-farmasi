<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_activities', function (Blueprint $table): void {
            $table->text('personal_notes')->nullable()->after('description');
            $table->text('revision_reason')->nullable()->after('verification_status');
            $table->text('rejection_reason')->nullable()->after('revision_reason');
            $table->timestamp('verified_at')->nullable()->after('rejection_reason');
            $table->foreignId('verified_by_app_user_id')->nullable()->after('verified_at')->constrained('app_users')->nullOnDelete();
            $table->timestamp('archived_at')->nullable()->after('verified_by_app_user_id');
        });

        Schema::table('documents', function (Blueprint $table): void {
            $table->string('document_number')->nullable()->after('title');
            $table->date('document_date')->nullable()->after('document_number');
            $table->string('issuer')->nullable()->after('document_date');
            $table->foreignId('uploaded_by_app_user_id')->nullable()->after('sha256_checksum')->constrained('app_users')->nullOnDelete();
            $table->string('visibility')->default('PRIVATE')->after('verification_status')->index();
            $table->text('delete_reason')->nullable()->after('visibility');
            $table->timestamp('deleted_at')->nullable()->after('delete_reason')->index();
        });

        Schema::create('portfolio_verification_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('portfolio_activity_id')->constrained('portfolio_activities')->cascadeOnDelete();
            $table->string('from_status')->nullable()->index();
            $table->string('to_status')->index();
            $table->foreignId('actor_app_user_id')->nullable()->constrained('app_users')->nullOnDelete();
            $table->string('actor_core_user_id')->nullable()->index();
            $table->string('actor_role')->nullable()->index();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('portfolio_issue_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('portfolio_activity_id')->constrained('portfolio_activities')->cascadeOnDelete();
            $table->foreignId('reporter_app_user_id')->constrained('app_users')->cascadeOnDelete();
            $table->string('issue_type')->index();
            $table->text('description');
            $table->text('expected_value')->nullable();
            $table->foreignId('supporting_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('status')->default('OPEN')->index();
            $table->text('admin_response')->nullable();
            $table->foreignId('resolved_by_app_user_id')->nullable()->constrained('app_users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('portfolio_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('portfolio_activity_id')->constrained('portfolio_activities')->cascadeOnDelete();
            $table->string('participant_type')->index();
            $table->string('core_dosen_id')->nullable()->index();
            $table->string('external_name')->nullable();
            $table->string('student_identifier')->nullable()->index();
            $table->string('student_name')->nullable();
            $table->string('institution_name')->nullable();
            $table->string('role')->index();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('participant_fingerprint', 64);
            $table->timestamps();
            $table->unique(['portfolio_activity_id', 'participant_fingerprint'], 'portfolio_participant_unique');
        });

        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('normalized_name')->unique();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('portfolio_activity_tag', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('portfolio_activity_id')->constrained('portfolio_activities')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['portfolio_activity_id', 'tag_id']);
        });

        Schema::create('document_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('disk');
            $table->string('path', 1000);
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('sha256_checksum', 64)->index();
            $table->foreignId('uploaded_by_app_user_id')->nullable()->constrained('app_users')->nullOnDelete();
            $table->string('source_app')->nullable()->index();
            $table->string('source_record_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['document_id', 'version_number']);
        });

        Schema::table('inbox_items', function (Blueprint $table): void {
            $table->timestamp('read_at')->nullable()->after('status');
            $table->json('metadata')->nullable()->after('action_url');
        });

        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->string('source_type')->default('MANUAL')->after('location')->index();
            $table->string('meeting_url', 1000)->nullable()->after('source_type');
            $table->json('metadata')->nullable()->after('source_record_id');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->dropColumn(['source_type', 'meeting_url', 'metadata']);
        });

        Schema::table('inbox_items', function (Blueprint $table): void {
            $table->dropColumn(['read_at', 'metadata']);
        });

        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('portfolio_activity_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('portfolio_participants');
        Schema::dropIfExists('portfolio_issue_reports');
        Schema::dropIfExists('portfolio_verification_histories');

        Schema::table('documents', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('uploaded_by_app_user_id');
            $table->dropColumn(['document_number', 'document_date', 'issuer', 'visibility', 'delete_reason', 'deleted_at']);
        });

        Schema::table('portfolio_activities', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('verified_by_app_user_id');
            $table->dropColumn(['personal_notes', 'revision_reason', 'rejection_reason', 'verified_at', 'archived_at']);
        });
    }
};
