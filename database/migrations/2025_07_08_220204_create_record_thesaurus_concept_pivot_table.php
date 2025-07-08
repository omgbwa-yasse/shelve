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
        Schema::create('record_thesaurus_concept', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')
                  ->constrained('records')
                  ->onDelete('cascade')
                  ->comment('Clé étrangère vers le record');
            $table->foreignId('concept_id')
                  ->constrained('thesaurus_concepts')
                  ->onDelete('cascade')
                  ->comment('Clé étrangère vers le concept du thésaurus');
            $table->decimal('weight', 3, 2)->default(1.0)->comment('Poids de la relation (0.0 à 1.0)');
            $table->string('context', 100)->nullable()->comment('Contexte de la relation (manuel, automatique, etc.)');
            $table->text('extraction_note')->nullable()->comment('Note sur l\'extraction du terme');
            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index(['record_id', 'weight']);
            $table->index(['concept_id', 'weight']);
            
            // Empêcher les doublons
            $table->unique(['record_id', 'concept_id'], 'unique_record_concept');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('record_thesaurus_concept');
    }
};
