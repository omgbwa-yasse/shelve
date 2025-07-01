<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTaskUsersTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:update-users-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Met à jour la table task_users avec les données de task_assignments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mise à jour de la table task_users...');

        // Vérifier si la table existe
        if (!Schema::hasTable('task_users')) {
            $this->error('La table task_users n\'existe pas.');
            return 1;
        }

        // Vérifier la structure actuelle
        $columns = Schema::getColumnListing('task_users');
        $this->info('Colonnes actuelles: ' . implode(', ', $columns));

        try {
            // On sauvegarde d'abord les données existantes
            $this->info('Sauvegarde des données existantes...');
            $existingData = DB::table('task_users')->get();
            $this->info('Nombre d\'enregistrements sauvegardés: ' . count($existingData));

            // Ajouter les colonnes ID et timestamps si elles n'existent pas
            if (!in_array('id', $columns)) {
                $this->info('Ajout de la colonne ID...');
                DB::statement('ALTER TABLE task_users ADD COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
            }

            if (!in_array('created_at', $columns)) {
                $this->info('Ajout des colonnes timestamps...');
                DB::statement('ALTER TABLE task_users ADD COLUMN created_at TIMESTAMP NULL');
                DB::statement('ALTER TABLE task_users ADD COLUMN updated_at TIMESTAMP NULL');

                // Mettre à jour les timestamps pour les enregistrements existants
                DB::statement('UPDATE task_users SET created_at = NOW(), updated_at = NOW()');
            }

            // Ajouter l'index unique s'il n'existe pas déjà
            try {
                $this->info('Ajout de l\'index unique...');
                DB::statement('ALTER TABLE task_users ADD UNIQUE INDEX task_user_unique (task_id, user_id)');
            } catch (\Exception $e) {
                $this->warn('L\'index existe probablement déjà: ' . $e->getMessage());
            }

            // Synchroniser avec les données de task_assignments
            $this->info('Synchronisation des données avec task_assignments...');

            // Vider la table
            DB::statement('DELETE FROM task_users');

            // Réinsérer les données depuis task_assignments
            $inserted = DB::statement("
                INSERT INTO task_users (task_id, user_id, created_at, updated_at)
                SELECT task_id, assignee_user_id, NOW(), NOW()
                FROM task_assignments
                WHERE assignee_type = 'user'
            ");

            $count = DB::table('task_users')->count();
            $this->info('Données réinsérées avec succès. Nombre d\'enregistrements: ' . $count);

            $this->info('Mise à jour terminée avec succès.');
            return 0;
        } catch (\Exception $e) {
            $this->error('Une erreur est survenue: ' . $e->getMessage());
            return 1;
        }
    }
}
