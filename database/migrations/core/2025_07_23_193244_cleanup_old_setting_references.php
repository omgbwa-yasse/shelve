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
        // Cette migration nettoie les références à l'ancienne table setting_values
        // qui pourrait encore exister dans certaines configurations

        if (Schema::hasTable('setting_values')) {
            Schema::dropIfExists('setting_values');
        }

        // Nettoyer les index qui pourraient ne plus être nécessaires
        Schema::table('settings', function (Blueprint $table) {
            // S'assurer que les nouveaux index existent
            if (!Schema::hasColumn('settings', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('settings', 'organisation_id')) {
                $table->foreignId('organisation_id')->nullable()->constrained('organisations')->onDelete('cascade');
            }
            if (!Schema::hasColumn('settings', 'value')) {
                $table->json('value')->nullable()->comment('Current value (null = use default_value)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionnel: recréer l'ancienne structure si nécessaire
        // Mais généralement on ne revient pas en arrière sur ce type de refactoring
    }
};
