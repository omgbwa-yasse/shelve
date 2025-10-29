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
        Schema::create('opac_configuration_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');
            $table->foreignId('configuration_id')->constrained('opac_configurations')->onDelete('cascade');
            $table->text('value')->nullable(); // Valeur de la configuration pour cette organisation
            $table->json('json_value')->nullable(); // Valeur JSON pour les types complexes
            $table->boolean('is_active')->default(true); // Valeur active ou non
            $table->timestamp('last_modified_at')->nullable(); // Dernière modification
            $table->foreignId('modified_by')->nullable()->constrained('users')->onDelete('set null'); // Utilisateur qui a modifié
            $table->timestamps();

            // Index avec nom personnalisé plus court
            $table->unique(['organisation_id', 'configuration_id'], 'opac_config_values_org_config_unique');
            $table->index('is_active');
            $table->index('last_modified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opac_configuration_values');
    }
};
