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
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->string('config_key');
            $table->json('config_value');
            $table->string('config_type')->default('mixed');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index pour les performances
            $table->index(['organisation_id', 'config_key']);
            $table->index(['organisation_id', 'is_active']);

            // Contrainte d'unicité
            $table->unique(['organisation_id', 'config_key']);
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
