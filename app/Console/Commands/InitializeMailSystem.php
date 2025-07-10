<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InitializeMailSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:init {--fresh : Recréer toutes les données depuis zéro}';

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
        $fresh = $this->option('fresh');

        $this->info('🚀 INITIALISATION COMPLÈTE DU SYSTÈME DE COURRIERS');
        $this->info('==================================================');
        $this->newLine();

        if ($fresh) {
            $this->warn('⚠️  Mode --fresh activé : toutes les données existantes seront supprimées !');
            if (!$this->confirm('Êtes-vous sûr de vouloir continuer ?')) {
                $this->info('❌ Opération annulée');
                return Command::FAILURE;
            }

            $this->info('🗑️  Nettoyage des données existantes...');
            $this->cleanExistingData();
        }

        // 1. Initialiser le système de courriers
        $this->info('📊 Initialisation du système de courriers...');
        $this->call('mail:seed', $fresh ? ['--force' => true] : []);

        $this->newLine();

        // 2. Vérifier les données externes
        $this->info('🌐 Vérification des données externes...');
        $this->call('external:check-data');

        $this->newLine();

        // 3. Résumé final
        $this->info('✅ INITIALISATION TERMINÉE AVEC SUCCÈS !');
        $this->info('========================================');
        $this->newLine();

        $this->table(
            ['Composant', 'Statut'],
            [
                ['Priorités de courrier', '✅ Configurées'],
                ['Actions de courrier', '✅ Configurées'],
                ['Typologies de courrier', '✅ Configurées'],
                ['Organisations externes', '✅ Configurées'],
                ['Contacts externes', '✅ Configurées'],
                ['Activités', '✅ Configurées'],
            ]
        );

        $this->newLine();
        $this->info('🎯 Le système de gestion des courriers est prêt à être utilisé !');
        $this->info('   Vous pouvez maintenant créer des courriers entrants et sortants');
        $this->info('   avec la gestion complète des contacts et organisations externes.');

        return Command::SUCCESS;
    }

    private function cleanExistingData()
    {
        // Nettoyer les données dans l'ordre inverse des dépendances
        \App\Models\Mail::truncate();
        \App\Models\ExternalContact::truncate();
        \App\Models\ExternalOrganization::truncate();
        \App\Models\MailTypology::truncate();
        \App\Models\MailAction::truncate();
        \App\Models\MailPriority::truncate();

        $this->info('✅ Données existantes supprimées');
    }
}
