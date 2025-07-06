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
        Schema::table('terms', function (Blueprint $table) {
            // Modifier la colonne preferred_label pour augmenter sa taille à 255 caractères
            $table->string('preferred_label', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terms', function (Blueprint $table) {
            // Revenir à 100 caractères si nécessaire
            $table->string('preferred_label', 100)->change();
        });
    }
};
