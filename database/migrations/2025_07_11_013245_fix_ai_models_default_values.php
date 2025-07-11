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
        Schema::table('ai_models', function (Blueprint $table) {
            // Ajouter des valeurs par défaut aux champs requis
            $table->string('version')->default('1.0.0')->change();
            $table->json('capabilities')->nullable()->change();
            $table->string('api_type')->default('local')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_models', function (Blueprint $table) {
            // Retirer les valeurs par défaut
            $table->string('version')->default(null)->change();
            $table->json('capabilities')->nullable(false)->change();
            $table->string('api_type')->default(null)->change();
        });
    }
};
