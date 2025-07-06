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
        // Supprimer toutes les tables liées au thésaurus dans le bon ordre (contraintes FK)

        // Tables de relations d'abord
        Schema::dropIfExists('term_related');
        Schema::dropIfExists('term_equivalent');
        Schema::dropIfExists('term_translations');
        Schema::dropIfExists('record_term'); // Pivot table with records

        // Tables dépendantes
        Schema::dropIfExists('external_alignments');
        Schema::dropIfExists('non_descriptors');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('hierarchical_relations');
        Schema::dropIfExists('associative_relations');

        // Table principale des termes
        Schema::dropIfExists('terms');

        // Tables de référence
        Schema::dropIfExists('term_types');
        Schema::dropIfExists('term_categories');
        Schema::dropIfExists('term_equivalent_types');

        // Tables du nouveau système (si elles existent)
        Schema::dropIfExists('concept_schemes');
        Schema::dropIfExists('concepts');
        Schema::dropIfExists('concept_labels');
        Schema::dropIfExists('concept_hierarchies');
        Schema::dropIfExists('concept_associations');
        Schema::dropIfExists('concept_collections');
        Schema::dropIfExists('thesaurus_imports');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer les tables principales (version simplifiée pour rollback)

        Schema::create('term_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('term_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('term_equivalent_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->nullable(false);
            $table->string('name', 100)->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('language_id')->nullable(false);
            $table->unsignedBigInteger('category_id')->nullable(false);
            $table->unsignedBigInteger('type_id')->nullable(false);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
        });
    }
};
