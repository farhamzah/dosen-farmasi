<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lecturer_educations')) {
            Schema::create('lecturer_educations', function (Blueprint $table): void {
                $table->id();
                $table->string('lecturer_core_id')->index();
                $table->string('level')->index();
                $table->string('institution_name');
                $table->string('study_program')->nullable();
                $table->string('city')->nullable();
                $table->string('country')->nullable();
                $table->unsignedSmallInteger('start_year')->nullable();
                $table->unsignedSmallInteger('end_year')->nullable();
                $table->string('graduation_status')->default('LULUS')->index();
                $table->string('degree')->nullable();
                $table->string('certificate_number')->nullable();
                $table->date('certificate_date')->nullable();
                $table->text('thesis_title')->nullable();
                $table->json('supervisors')->nullable();
                $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
                $table->string('source_type')->default('MANUAL')->index();
                $table->string('source_app')->nullable()->index();
                $table->string('source_record_id')->nullable()->index();
                $table->string('verification_status')->default('DRAFT')->index();
                $table->string('visibility')->default('INTERNAL')->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('lecturer_functional_positions')) {
            Schema::create('lecturer_functional_positions', function (Blueprint $table): void {
                $table->id();
                $table->string('lecturer_core_id')->index();
                $table->string('position_name')->index();
                $table->date('effective_date')->nullable()->index();
                $table->decimal('credit_score', 8, 2)->nullable();
                $table->string('decree_number')->nullable();
                $table->date('decree_date')->nullable();
                $table->string('signing_official')->nullable();
                $table->string('unit')->nullable();
                $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
                $table->boolean('is_active')->default(false)->index();
                $table->string('source_type')->default('MANUAL')->index();
                $table->string('source_app')->nullable()->index();
                $table->string('source_record_id')->nullable()->index();
                $table->string('verification_status')->default('DRAFT')->index();
                $table->string('visibility')->default('INTERNAL')->index();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('lecturer_structural_positions')) {
            Schema::create('lecturer_structural_positions', function (Blueprint $table): void {
                $table->id();
                $table->string('lecturer_core_id')->index();
                $table->string('position_name')->index();
                $table->string('unit')->nullable();
                $table->date('start_date')->nullable()->index();
                $table->date('end_date')->nullable();
                $table->string('decree_number')->nullable();
                $table->date('decree_date')->nullable();
                $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
                $table->boolean('is_active')->default(false)->index();
                $table->string('source_type')->default('MANUAL')->index();
                $table->string('source_app')->nullable()->index();
                $table->string('source_record_id')->nullable()->index();
                $table->string('verification_status')->default('DRAFT')->index();
                $table->string('visibility')->default('INTERNAL')->index();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('lecturer_employments')) {
            Schema::create('lecturer_employments', function (Blueprint $table): void {
                $table->id();
                $table->string('lecturer_core_id')->index();
                $table->string('institution_name');
                $table->string('position_name')->nullable();
                $table->string('employment_type')->nullable()->index();
                $table->date('start_date')->nullable()->index();
                $table->date('end_date')->nullable();
                $table->text('description')->nullable();
                $table->string('source_type')->default('MANUAL')->index();
                $table->string('source_app')->nullable()->index();
                $table->string('source_record_id')->nullable()->index();
                $table->string('verification_status')->default('DRAFT')->index();
                $table->string('visibility')->default('INTERNAL')->index();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('lecturer_expertise_areas')) {
            Schema::create('lecturer_expertise_areas', function (Blueprint $table): void {
                $table->id();
                $table->string('lecturer_core_id')->index();
                $table->string('knowledge_family')->nullable();
                $table->string('knowledge_tree')->nullable();
                $table->string('knowledge_group')->nullable();
                $table->string('knowledge_branch')->nullable();
                $table->string('knowledge_leaf')->nullable();
                $table->string('primary_expertise')->nullable()->index();
                $table->json('specializations')->nullable();
                $table->json('research_topics')->nullable();
                $table->json('practical_skills')->nullable();
                $table->json('collaboration_interests')->nullable();
                $table->boolean('is_primary')->default(false)->index();
                $table->string('source_type')->default('MANUAL')->index();
                $table->string('source_app')->nullable()->index();
                $table->string('source_record_id')->nullable()->index();
                $table->string('verification_status')->default('DRAFT')->index();
                $table->string('visibility')->default('INTERNAL')->index();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('lecturer_certifications')) {
            Schema::create('lecturer_certifications', function (Blueprint $table): void {
                $table->id();
                $table->string('lecturer_core_id')->index();
                $table->string('category')->index();
                $table->string('name');
                $table->string('issuer')->nullable();
                $table->string('certificate_number')->nullable();
                $table->date('issued_at')->nullable();
                $table->date('expires_at')->nullable();
                $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
                $table->string('status')->default('AKTIF')->index();
                $table->string('source_type')->default('MANUAL')->index();
                $table->string('source_app')->nullable()->index();
                $table->string('source_record_id')->nullable()->index();
                $table->string('verification_status')->default('DRAFT')->index();
                $table->string('visibility')->default('INTERNAL')->index();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('lecturer_external_identifiers')) {
            Schema::create('lecturer_external_identifiers', function (Blueprint $table): void {
                $table->id();
                $table->string('lecturer_core_id')->index();
                $table->string('identifier_type')->index();
                $table->string('identifier_value');
                $table->string('profile_url', 1000)->nullable();
                $table->string('source_type')->default('MANUAL')->index();
                $table->string('source_app')->nullable()->index();
                $table->string('source_record_id')->nullable()->index();
                $table->string('verification_status')->default('DRAFT')->index();
                $table->string('visibility')->default('INTERNAL')->index();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['lecturer_core_id', 'identifier_type'], 'lecturer_ext_ids_lecturer_type_unique');
            });
        }

        if (! Schema::hasTable('profile_visibility_settings')) {
            Schema::create('profile_visibility_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('lecturer_core_id')->unique();
                $table->json('section_visibility')->nullable();
                $table->json('field_visibility')->nullable();
                $table->boolean('public_profile_enabled')->default(false)->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_visibility_settings');
        Schema::dropIfExists('lecturer_external_identifiers');
        Schema::dropIfExists('lecturer_certifications');
        Schema::dropIfExists('lecturer_expertise_areas');
        Schema::dropIfExists('lecturer_employments');
        Schema::dropIfExists('lecturer_structural_positions');
        Schema::dropIfExists('lecturer_functional_positions');
        Schema::dropIfExists('lecturer_educations');
    }
};
