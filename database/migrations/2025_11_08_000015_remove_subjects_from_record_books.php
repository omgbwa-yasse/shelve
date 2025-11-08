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
        Schema::table('record_books', function (Blueprint $table) {
            // Supprimer l'ancien champ JSON subjects
            $table->dropColumn('subjects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('record_books', function (Blueprint $table) {
            // Restaurer le champ subjects
            $table->json('subjects')->nullable()->comment('Sujets/Thèmes (JSON array)')->after('lcc');
        });
    }
};
