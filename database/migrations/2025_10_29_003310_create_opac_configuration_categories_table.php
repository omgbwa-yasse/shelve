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
        Schema::create('opac_configuration_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // Nom de la catégorie (ex: 'display', 'search', 'security')
            $table->string('label', 255); // Libellé affiché dans l'interface
            $table->text('description')->nullable(); // Description de la catégorie
            $table->string('icon', 50)->nullable(); // Icône FontAwesome pour l'interface
            $table->integer('sort_order')->default(0); // Ordre d'affichage
            $table->boolean('is_active')->default(true); // Catégorie active ou non
            $table->timestamps();
            
            // Index
            $table->unique('name');
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opac_configuration_categories');
    }
};
