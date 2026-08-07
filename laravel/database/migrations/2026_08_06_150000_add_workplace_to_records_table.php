<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Docs d'un espace de travail : les notices (`records`) deviennent la
     * bibliothèque Documents du workplace. `workplace_id` rattache la notice à
     * l'espace, `is_workplace_folder` distingue dossier / fichier (la hiérarchie
     * des dossiers réutilise `parent_id`, déjà présent). Les fichiers sont portés
     * par la pivot `record_physical_attachment` (table `attachments`).
     */
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->foreignId('workplace_id')
                ->nullable()
                ->after('organisation_id')
                ->constrained('workplaces')
                ->nullOnDelete();

            $table->boolean('is_workplace_folder')
                ->default(false)
                ->after('workplace_id');

            $table->index('workplace_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropIndex(['workplace_id']);
            $table->dropColumn(['is_workplace_folder', 'workplace_id']);
        });
    }
};
