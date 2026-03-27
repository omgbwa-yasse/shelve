<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupDevEnvironment extends Command
{
    protected $signature = 'app:setup
                            {--fresh : Supprime toutes les tables et re-migre (destructif)}
                            {--seed-all : Seed toutes les données y compris les données de test}';

    protected $description = 'Initialise l\'environnement : .env, clé app, SQLite, migrations, paramètres de base';

    public function handle(): int
    {
        $this->line('');
        $this->line('============================================');
        $this->info('  SHELVE - Initialisation de l\'environnement');
        $this->line('============================================');
        $this->line('');

        // Étape 1 : fichier .env
        $this->comment('[1/6] Vérification du fichier .env');
        $envPath = base_path('.env');
        $envExample = base_path('.env.example');

        if (! file_exists($envPath)) {
            if (! file_exists($envExample)) {
                $this->error('  .env.example introuvable. Impossible de continuer.');
                return self::FAILURE;
            }
            copy($envExample, $envPath);
            $this->info('  .env créé depuis .env.example');
        } else {
            $this->line('  .env existe déjà — ignoré');
        }

        // Étape 2 : APP_KEY
        $this->line('');
        $this->comment('[2/6] Vérification de APP_KEY');
        $envContent = file_get_contents($envPath);
        $keyIsSet = (bool) preg_match('/^APP_KEY=base64:.+/m', $envContent);

        if (! $keyIsSet) {
            Artisan::call('key:generate', ['--force' => true, '--ansi' => true]);
            $this->info('  APP_KEY généré');
        } else {
            $this->line('  APP_KEY déjà défini — ignoré');
        }

        // Étape 3 : fichier SQLite + dossiers storage requis
        $this->line('');
        $this->comment('[3/6] Vérification de la base de données SQLite');
        $sqlitePath = database_path('database.sqlite');

        if (! file_exists($sqlitePath)) {
            touch($sqlitePath);
            $this->info('  Fichier database/database.sqlite créé');
        } else {
            $this->line('  database.sqlite existe déjà — ignoré');
        }

        // Créer les dossiers storage nécessaires
        $storageDirs = [
            storage_path('app/public'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            storage_path('tntsearch'),
        ];
        foreach ($storageDirs as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Étape 4 : migrations
        $this->line('');
        $this->comment('[4/6] Exécution des migrations');
        if ($this->option('fresh')) {
            $this->warn('  Option --fresh : suppression de toutes les tables...');
            Artisan::call('migrate:fresh', ['--force' => true, '--ansi' => true]);
        } else {
            Artisan::call('migrate', ['--force' => true, '--ansi' => true]);
        }
        $output = trim(Artisan::output());
        if ($output) {
            $this->line('  ' . str_replace("\n", "\n  ", $output));
        }
        $this->info('  Migrations terminées');

        // Étape 5 : seeders
        $this->line('');
        $this->comment('[5/6] Exécution des seeders');
        $seederClass = $this->option('seed-all')
            ? \Database\Seeders\DatabaseSeeder::class
            : \Database\Seeders\ParametersSeeder::class;

        $label = $this->option('seed-all') ? 'DatabaseSeeder (toutes les données)' : 'ParametersSeeder (paramètres seulement)';
        $this->line("  Seeder : {$label}");

        Artisan::call('db:seed', [
            '--class' => $seederClass,
            '--force' => true,
            '--ansi'  => true,
        ]);
        $output = trim(Artisan::output());
        if ($output) {
            $this->line('  ' . str_replace("\n", "\n  ", $output));
        }
        $this->info('  Seeding terminé');

        // Étape 6 : vider les caches
        $this->line('');
        $this->comment('[6/6] Nettoyage des caches');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        $this->info('  Caches nettoyés');

        // Résumé
        $this->line('');
        $this->line('============================================');
        $this->info('  Installation terminée !');
        $this->line('============================================');
        $this->line('');
        $this->line('  Démarrer le serveur :  php artisan serve --port=8000');
        $this->line('  Accès :                http://localhost:8000');
        $this->line('');

        return self::SUCCESS;
    }
}
