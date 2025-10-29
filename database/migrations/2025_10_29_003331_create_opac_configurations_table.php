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
        Schema::create('opac_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('opac_configuration_categories')->onDelete('cascade');
            $table->string('key', 100); // Clé de configuration (ex: 'show_statistics', 'items_per_page')
            $table->string('label', 255); // Libellé affiché dans l'interface
            $table->text('description')->nullable(); // Description de la configuration
            $table->enum('type', ['boolean', 'integer', 'string', 'text', 'json', 'select', 'multiselect']); // Type de données
            $table->json('options')->nullable(); // Options pour les champs select/multiselect
            $table->text('default_value')->nullable(); // Valeur par défaut
            $table->json('validation_rules')->nullable(); // Règles de validation Laravel
            $table->integer('sort_order')->default(0); // Ordre d'affichage dans la catégorie
            $table->boolean('is_required')->default(false); // Configuration obligatoire
            $table->boolean('is_active')->default(true); // Configuration active
            $table->timestamps();

            // Index
            $table->unique(['category_id', 'key']);
            $table->index('type');
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opac_configurations');
    }
};
