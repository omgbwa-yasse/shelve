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
        // Table des collections SKOS
        if (!Schema::hasTable('thesaurus_collections')) {
            Schema::create('thesaurus_collections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scheme_id')
                    ->constrained('thesaurus_schemes')
                    ->onDelete('cascade');
                $table->string('uri', 191)->unique()->comment('URI unique de la collection SKOS');
                $table->boolean('ordered')->default(false)->comment('Indique si la collection est ordonnée');
                $table->timestamps();
                $table->index('uri');
            });
        }

        // Table des labels de collections
        if (!Schema::hasTable('thesaurus_collection_labels')) {
            Schema::create('thesaurus_collection_labels', function (Blueprint $table) {
                $table->id();
                $table->foreignId('collection_id')
                    ->constrained('thesaurus_collections')
                    ->onDelete('cascade');
                $table->string('label', 500)->comment('Forme littérale du label de la collection');
                $table->string('label_type', 50)->comment('Type de label (prefLabel, altLabel, hiddenLabel)');
                $table->string('language', 10)->default('fr')->comment('Langue du label');
                $table->timestamps();
                $table->index(['collection_id', 'language', 'label_type'], 'thes_coll_labels_index');
            });
        }

        // Table des membres de collection (relation collection -> concept)
        if (!Schema::hasTable('thesaurus_collection_members')) {
            Schema::create('thesaurus_collection_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('collection_id')
                    ->constrained('thesaurus_collections')
                    ->onDelete('cascade');
                $table->foreignId('concept_id')
                    ->constrained('thesaurus_concepts')
                    ->onDelete('cascade');
                $table->integer('position')->nullable()->comment('Position pour les collections ordonnées');
                $table->timestamps();
                $table->unique(['collection_id', 'concept_id'], 'thes_coll_concept_unique');
                $table->index(['collection_id', 'position'], 'thes_coll_pos_index');
            });
        }

        // Table des sous-collections (collections imbriquées)
        if (!Schema::hasTable('thesaurus_nested_collections')) {
            Schema::create('thesaurus_nested_collections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_collection_id')
                    ->constrained('thesaurus_collections')
                    ->onDelete('cascade');
                $table->foreignId('child_collection_id')
                    ->constrained('thesaurus_collections')
                    ->onDelete('cascade');
                $table->integer('position')->nullable()->comment('Position pour les collections ordonnées');
                $table->timestamps();
                $table->unique(['parent_collection_id', 'child_collection_id'], 'thes_nested_coll_unique');
                $table->index(['parent_collection_id', 'position'], 'thes_parent_pos_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thesaurus_nested_collections');
        Schema::dropIfExists('thesaurus_collection_members');
        Schema::dropIfExists('thesaurus_collection_labels');
        Schema::dropIfExists('thesaurus_collections');
    }
};
