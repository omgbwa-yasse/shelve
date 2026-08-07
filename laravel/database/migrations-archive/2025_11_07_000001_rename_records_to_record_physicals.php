<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cette migration renomme la table 'records' en 'record_physicals' ainsi que
     * toutes les tables pivot associées pour refléter la nouvelle architecture modulaire.
     */
    public function up(): void
    {
        // Désactiver temporairement les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // 1. Renommer la table principale
            Schema::rename('records', 'record_physicals');

            // 2. Renommer les tables pivot
            if (Schema::hasTable('record_author')) {
                Schema::rename('record_author', 'record_physical_author');
            }

            if (Schema::hasTable('record_attachment')) {
                Schema::rename('record_attachment', 'record_physical_attachment');
            }

            if (Schema::hasTable('record_keyword')) {
                Schema::rename('record_keyword', 'record_physical_keyword');
            }

            if (Schema::hasTable('record_thesaurus_concept')) {
                Schema::rename('record_thesaurus_concept', 'record_physical_thesaurus_concept');
            }

            if (Schema::hasTable('record_container')) {
                Schema::rename('record_container', 'record_physical_container');
            }

            if (Schema::hasTable('record_term')) {
                Schema::rename('record_term', 'record_physical_term');
            }

            if (Schema::hasTable('record_links')) {
                Schema::rename('record_links', 'record_physical_links');
            }

            // 3. Log de la migration
            DB::table('migrations')->insert([
                'migration' => '2025_11_07_000001_rename_records_to_record_physicals',
                'batch' => DB::table('migrations')->max('batch') + 1
            ]);

            // Réactiver les contraintes
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

        } catch (\Exception $e) {
            // En cas d'erreur, réactiver les contraintes et relancer l'exception
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * Restaure les noms originaux des tables.
     */
    public function down(): void
    {
        // Désactiver temporairement les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            // Restaurer les noms originaux dans l'ordre inverse
            if (Schema::hasTable('record_physical_links')) {
                Schema::rename('record_physical_links', 'record_links');
            }

            if (Schema::hasTable('record_physical_term')) {
                Schema::rename('record_physical_term', 'record_term');
            }

            if (Schema::hasTable('record_physical_container')) {
                Schema::rename('record_physical_container', 'record_container');
            }

            if (Schema::hasTable('record_physical_thesaurus_concept')) {
                Schema::rename('record_physical_thesaurus_concept', 'record_thesaurus_concept');
            }

            if (Schema::hasTable('record_physical_keyword')) {
                Schema::rename('record_physical_keyword', 'record_keyword');
            }

            if (Schema::hasTable('record_physical_attachment')) {
                Schema::rename('record_physical_attachment', 'record_attachment');
            }

            if (Schema::hasTable('record_physical_author')) {
                Schema::rename('record_physical_author', 'record_author');
            }

            // Restaurer la table principale en dernier
            Schema::rename('record_physicals', 'records');

            // Réactiver les contraintes
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

        } catch (\Exception $e) {
            // En cas d'erreur, réactiver les contraintes et relancer l'exception
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
    }
};
