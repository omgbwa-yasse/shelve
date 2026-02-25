<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Profils pour les documents numériques
        Schema::create('record_digital_document_metadata_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedBigInteger('metadata_definition_id');
            $table->boolean('mandatory')->default(false);
            $table->boolean('visible')->default(true);
            $table->boolean('readonly')->default(false);
            $table->text('default_value')->nullable();
            $table->json('validation_rules')->nullable()->comment('Additional validation rules specific to this profile');
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['document_type_id', 'metadata_definition_id'], 'doc_meta_prof_unique');
            $table->index('mandatory');
            $table->index('sort_order');

            // Foreign keys with custom names
            $table->foreign('document_type_id', 'doc_meta_prof_type_fk')
                ->references('id')->on('record_digital_document_types')->cascadeOnDelete();
            $table->foreign('metadata_definition_id', 'doc_meta_prof_def_fk')
                ->references('id')->on('metadata_definitions')->cascadeOnDelete();
            $table->foreign('created_by', 'doc_meta_prof_creator_fk')
                ->references('id')->on('users');
            $table->foreign('updated_by', 'doc_meta_prof_updater_fk')
                ->references('id')->on('users');
        });

        // Profils pour les dossiers numériques
        Schema::create('record_digital_folder_metadata_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_type_id');
            $table->unsignedBigInteger('metadata_definition_id');
            $table->boolean('mandatory')->default(false);
            $table->boolean('visible')->default(true);
            $table->boolean('readonly')->default(false);
            $table->text('default_value')->nullable();
            $table->json('validation_rules')->nullable()->comment('Additional validation rules specific to this profile');
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['folder_type_id', 'metadata_definition_id'], 'folder_meta_prof_unique');
            $table->index('mandatory');
            $table->index('sort_order');

            // Foreign keys with custom names
            $table->foreign('folder_type_id', 'folder_meta_prof_type_fk')
                ->references('id')->on('record_digital_folder_types')->cascadeOnDelete();
            $table->foreign('metadata_definition_id', 'folder_meta_prof_def_fk')
                ->references('id')->on('metadata_definitions')->cascadeOnDelete();
            $table->foreign('created_by', 'folder_meta_prof_creator_fk')
                ->references('id')->on('users');
            $table->foreign('updated_by', 'folder_meta_prof_updater_fk')
                ->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_digital_folder_metadata_profiles');
        Schema::dropIfExists('record_digital_document_metadata_profiles');
    }
};
