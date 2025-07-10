<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\MailSystemSeeder;
use Database\Seeders\ExternalContactsSeeder;

class SeedMailSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:seed {--force : Forcer l\'exécution même si des données existent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialiser complètement le système de courriers avec toutes les données nécessaires';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        $this->info('🚀 INITIALISATION DU SYSTÈME DE COURRIERS');
        $this->newLine();

        if (!$force && $this->hasExistingData()) {
            if (!$this->confirm('Des données existent déjà. Voulez-vous continuer et potentiellement créer des doublons ?')) {
                $this->info('❌ Opération annulée');
                return Command::FAILURE;
            }
        }

        $this->info('📊 Exécution du seeder principal...');
        $this->call('db:seed', ['--class' => 'MailSystemSeeder']);

        $this->info('🌐 Exécution du seeder contacts externes...');
        $this->call('db:seed', ['--class' => 'ExternalContactsSeeder']);

        $this->newLine();
        $this->info('✅ Initialisation terminée !');
        $this->newLine();

        $this->info('🔍 Vérification du système...');
        $this->call('mail:check-system');

        return Command::SUCCESS;
    }

    private function hasExistingData()
    {
        return \App\Models\MailTypology::count() > 0 || 
               \App\Models\MailPriority::count() > 0 || 
               \App\Models\MailAction::count() > 0 ||
               \App\Models\ExternalOrganization::count() > 0;
    }
}
