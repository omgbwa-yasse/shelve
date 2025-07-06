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
        // Supprimer les tables de l'ancienne structure thésaurus dans l'ordre correct
        // (en commençant par les tables avec des clés étrangères)
        Schema::dropIfExists('external_alignments');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('non_descriptors');
        Schema::dropIfExists('associative_relations');
        Schema::dropIfExists('hierarchical_relations');
        Schema::dropIfExists('terms');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer les tables en cas de rollback (structure de l'ancienne migration)
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('preferred_label', 100);
            $table->text('definition')->nullable();
            $table->text('scope_note')->nullable();
            $table->text('history_note')->nullable();
            $table->text('example')->nullable();
            $table->text('editorial_note')->nullable();
            $table->enum('language', ['fr', 'en', 'es', 'de', 'it', 'pt'])->default('fr');
            $table->string('category', 100)->nullable();
            $table->enum('status', ['approved', 'candidate', 'deprecated'])->default('candidate');
            $table->string('notation', 50)->nullable();
            $table->boolean('is_top_term')->default(false);
            $table->timestamps();

            $table->index(['language', 'status']);
            $table->index('is_top_term');
        });

        Schema::create('hierarchical_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('broader_term_id');
            $table->unsignedBigInteger('narrower_term_id');
            $table->enum('relation_type', ['generic', 'partitive', 'instance'])->default('generic');
            $table->timestamps();

            $table->foreign('broader_term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('narrower_term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->unique(['broader_term_id', 'narrower_term_id']);
        });

        Schema::create('associative_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term1_id');
            $table->unsignedBigInteger('term2_id');
            $table->enum('relation_subtype', [
                'cause_effect', 'whole_part', 'action_agent', 'action_product',
                'action_object', 'action_location', 'science_object', 'object_property',
                'object_role', 'raw_material_product', 'process_neutralizer',
                'object_origin', 'concept_measurement', 'profession_person', 'general'
            ])->default('general');
            $table->timestamps();

            $table->foreign('term1_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('term2_id')->references('id')->on('terms')->onDelete('cascade');
            $table->unique(['term1_id', 'term2_id']);
        });

        Schema::create('non_descriptors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('descriptor_id');
            $table->string('non_descriptor_label', 100);
            $table->enum('relation_type', [
                'synonym', 'quasi_synonym', 'abbreviation', 'acronym',
                'scientific_name', 'common_name', 'brand_name', 'variant_spelling',
                'old_form', 'modern_form', 'antonym'
            ])->default('synonym');
            $table->boolean('hidden')->default(false);
            $table->timestamps();

            $table->foreign('descriptor_id')->references('id')->on('terms')->onDelete('cascade');
            $table->index(['descriptor_id', 'relation_type']);
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_term_id');
            $table->unsignedBigInteger('target_term_id');
            $table->timestamps();

            $table->foreign('source_term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('target_term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->unique(['source_term_id', 'target_term_id']);
        });

        Schema::create('external_alignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id');
            $table->string('external_uri', 500);
            $table->string('external_label', 200)->nullable();
            $table->string('external_vocabulary', 100);
            $table->enum('match_type', ['exact', 'close', 'broad', 'narrow', 'related'])->default('exact');
            $table->timestamps();

            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->index(['external_vocabulary', 'match_type']);
        });
    }
};
