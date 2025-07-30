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
        Schema::table('sorts', function (Blueprint $table) {
            // Modifier la colonne code pour accepter uniquement E, T, C
            $table->enum('code', ['E', 'T', 'C'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sorts', function (Blueprint $table) {
            // Revenir à une colonne string normale
            $table->string('code', 10)->change();
        });
    }
};
