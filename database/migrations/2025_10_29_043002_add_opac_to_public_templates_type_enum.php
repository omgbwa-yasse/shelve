<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modifier l'enum pour ajouter 'opac' aux types possibles
        DB::statement("ALTER TABLE public_templates MODIFY COLUMN type ENUM('page', 'email', 'notification', 'opac') NOT NULL DEFAULT 'page'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer 'opac' de l'enum et supprimer les templates OPAC
        DB::statement("DELETE FROM public_templates WHERE type = 'opac'");
        DB::statement("ALTER TABLE public_templates MODIFY COLUMN type ENUM('page', 'email', 'notification') NOT NULL DEFAULT 'page'");
    }
};
