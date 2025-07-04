<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table principale des termes (concepts)
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('preferred_label', 100); // Terme préféré/descripteur
            $table->text('definition')->nullable(); // Définition du concept
            $table->text('scope_note')->nullable(); // Note de portée/usage
            $table->text('history_note')->nullable(); // Note historique
            $table->text('example')->nullable(); // Exemple d'usage
            $table->text('editorial_note')->nullable(); // Note éditoriale
            $table->enum('language', ['fr', 'en', 'es', 'de', 'it', 'pt'])->default('fr');
            $table->string('category', 100)->nullable(); // Domaine/facette
            $table->enum('status', ['approved', 'candidate', 'deprecated'])->default('candidate');
            $table->string('notation', 50)->nullable(); // Code/notation du concept
            $table->boolean('is_top_term')->default(false); // Terme de tête
            $table->timestamps();

            $table->index(['language', 'status']);
            $table->index('is_top_term');
        });

        // Relations hiérarchiques (TG/TS, TGP/TSP, TGI/TSI)
        Schema::create('hierarchical_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('broader_term_id'); // Terme générique
            $table->unsignedBigInteger('narrower_term_id'); // Terme spécifique
            $table->enum('relation_type', ['generic', 'partitive', 'instance'])->default('generic');
            $table->timestamps();

            $table->foreign('broader_term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('narrower_term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->unique(['broader_term_id', 'narrower_term_id']);
        });

        // Relations associatives (TA/RT)
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

        // Table des termes non-descripteurs (synonymes)
        Schema::create('non_descriptors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('descriptor_id'); // Terme préféré
            $table->string('non_descriptor_label', 100); // Synonyme/variante
            $table->enum('relation_type', [
                'synonym', 'quasi_synonym', 'abbreviation', 'acronym',
                'scientific_name', 'common_name', 'brand_name', 'variant_spelling',
                'old_form', 'modern_form', 'antonym'
            ])->default('synonym');
            $table->boolean('hidden')->default(false); // Pour SKOS hiddenLabel
            $table->timestamps();

            $table->foreign('descriptor_id')->references('id')->on('terms')->onDelete('cascade');
            $table->index(['descriptor_id', 'relation_type']);
        });

        // Traductions entre langues
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_term_id');
            $table->unsignedBigInteger('target_term_id');
            $table->timestamps();

            $table->foreign('source_term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('target_term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->unique(['source_term_id', 'target_term_id']);
        });

        // Alignements avec référentiels externes (SKOS exactMatch, closeMatch, etc.)
        Schema::create('external_alignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id');
            $table->string('external_uri', 500); // URI du concept externe
            $table->string('external_label', 200)->nullable(); // Libellé du concept externe
            $table->string('external_vocabulary', 100); // Nom du vocabulaire externe
            $table->enum('match_type', ['exact', 'close', 'broad', 'narrow', 'related'])->default('exact');
            $table->timestamps();

            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->index(['external_vocabulary', 'match_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_alignments');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('non_descriptors');
        Schema::dropIfExists('associative_relations');
        Schema::dropIfExists('hierarchical_relations');
        Schema::dropIfExists('terms');
    }
};
