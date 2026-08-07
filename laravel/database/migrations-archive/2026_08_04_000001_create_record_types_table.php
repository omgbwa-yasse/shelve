<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1 — Unifier la couche Type.
     *
     * `record_types` remplace `record_digital_folder_types` + `record_digital_document_types`
     * (catalogues séparés, non reliés à un référentiel) par un catalogue unique ancré sur
     * `reference_lists` (équivalent `DomaineValeurs` / `TypeDocument` d'IntelliGID).
     * `is_container` porte la distinction dossier/document (ex-TypeDossier vs TypeDocument).
     */
    public function up(): void
    {
        Schema::create('record_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('record_types')->nullOnDelete();
            $table->foreignId('reference_list_id')->nullable()->constrained('reference_lists')->nullOnDelete();
            $table->boolean('is_container')->default(false); // true = ex-"dossier numérique" / TypeDossier
            $table->string('icon')->nullable();
            $table->string('color', 20)->nullable();
            $table->string('code_prefix')->nullable();
            $table->string('code_pattern')->nullable();
            $table->json('allowed_mime_types')->nullable();
            $table->json('allowed_extensions')->nullable();
            $table->unsignedBigInteger('max_file_size')->nullable();
            $table->boolean('requires_versioning')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->boolean('requires_signature')->default(false);
            $table->string('default_access_level', 20)->default('internal');
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            // Traçabilité de migration (retirée en Phase 7)
            $table->string('legacy_type', 50)->nullable(); // 'digital_document_type:ID' | 'digital_folder_type:ID'
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_container');
            $table->index('is_active');
            $table->index('display_order');
        });

        Schema::create('record_type_metadata_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_type_id')->constrained('record_types')->cascadeOnDelete();
            $table->foreignId('metadata_definition_id')->constrained('metadata_definitions')->cascadeOnDelete();
            $table->boolean('mandatory')->default(false);
            $table->boolean('visible')->default(true);
            $table->boolean('readonly')->default(false);
            $table->text('default_value')->nullable();
            $table->json('validation_rules')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['record_type_id', 'metadata_definition_id'], 'record_type_meta_prof_unique');
            $table->index('mandatory');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_type_metadata_profiles');
        Schema::dropIfExists('record_types');
    }
};
