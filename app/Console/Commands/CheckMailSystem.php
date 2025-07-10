<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MailTypology;
use App\Models\MailPriority;
use App\Models\MailAction;
use App\Models\ExternalOrganization;
use App\Models\ExternalContact;
use App\Models\Activity;

class CheckMailSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:check-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier tous les éléments du système de courriers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VÉRIFICATION DU SYSTÈME DE COURRIERS ===');
        $this->newLine();

        // Priorités
        $this->checkMailPriorities();
        $this->newLine();

        // Actions
        $this->checkMailActions();
        $this->newLine();

        // Typologies
        $this->checkMailTypologies();
        $this->newLine();

        // Contacts et organisations externes
        $this->checkExternalEntities();
        $this->newLine();

        // Activités
        $this->checkActivities();
        $this->newLine();

        $this->info('✅ Vérification terminée avec succès !');
        return Command::SUCCESS;
    }

    private function checkMailPriorities()
    {
        $this->info('📊 PRIORITÉS DE COURRIER');
        $priorities = MailPriority::orderBy('duration')->get();

        if ($priorities->isEmpty()) {
            $this->error('❌ Aucune priorité trouvée');
            return;
        }

        $this->table(
            ['Nom', 'Durée (jours)'],
            $priorities->map(function($priority) {
                return [$priority->name, $priority->duration];
            })
        );

        $this->info("✅ {$priorities->count()} priorités configurées");
    }

    private function checkMailActions()
    {
        $this->info('⚡ ACTIONS DE COURRIER');
        $actions = MailAction::orderBy('name')->get();

        if ($actions->isEmpty()) {
            $this->error('❌ Aucune action trouvée');
            return;
        }

        $this->table(
            ['Nom', 'Durée (jours)', 'À retourner', 'Description'],
            $actions->map(function($action) {
                return [
                    $action->name,
                    $action->duration,
                    $action->to_return ? 'Oui' : 'Non',
                    $action->description
                ];
            })
        );

        $this->info("✅ {$actions->count()} actions configurées");
    }

    private function checkMailTypologies()
    {
        $this->info('📋 TYPOLOGIES DE COURRIER');
        $typologies = MailTypology::with('activity')->orderBy('code')->get();

        if ($typologies->isEmpty()) {
            $this->error('❌ Aucune typologie trouvée');
            return;
        }

        // Grouper par catégorie
        $grouped = $typologies->groupBy(function($item) {
            $code = $item->code;
            if (in_array($code, ['CORR', 'INFO', 'CONV'])) return 'Correspondance';
            if (in_array($code, ['DSTG', 'DAID', 'DFOR', 'RECL'])) return 'Demandes';
            if (in_array($code, ['CERT', 'DECL', 'PERM'])) return 'Documents officiels';
            if (in_array($code, ['CRED', 'FACT', 'BUDG'])) return 'Financier';
            if (in_array($code, ['CAND', 'CONT', 'EVAL'])) return 'Ressources humaines';
            if (in_array($code, ['JUST', 'MISE'])) return 'Juridique';
            if (in_array($code, ['COMM', 'PRES', 'PART'])) return 'Communication';
            if (in_array($code, ['TECH', 'MAIN'])) return 'Technique';
            return 'Divers';
        });

        foreach ($grouped as $category => $items) {
            $this->line("📁 <fg=yellow>{$category}</>");
            foreach ($items as $typology) {
                $this->line("   • {$typology->code} - {$typology->name}");
            }
            $this->newLine();
        }

        $this->info("✅ {$typologies->count()} typologies configurées");
    }

    private function checkExternalEntities()
    {
        $this->info('🌐 ENTITÉS EXTERNES');

        $organizations = ExternalOrganization::count();
        $contacts = ExternalContact::count();
        $contactsWithOrg = ExternalContact::whereNotNull('external_organization_id')->count();
        $independentContacts = ExternalContact::whereNull('external_organization_id')->count();

        $this->table(
            ['Type', 'Nombre'],
            [
                ['Organisations externes', $organizations],
                ['Contacts externes (total)', $contacts],
                ['Contacts avec organisation', $contactsWithOrg],
                ['Contacts indépendants', $independentContacts]
            ]
        );

        if ($organizations > 0 && $contacts > 0) {
            $this->info("✅ Entités externes configurées");
        } else {
            $this->warn("⚠️  Peu ou pas d'entités externes configurées");
        }
    }

    private function checkActivities()
    {
        $this->info('📁 ACTIVITÉS');
        $activities = Activity::count();
        $activitiesWithTypologies = Activity::whereHas('mailTypologies')->count();

        $this->table(
            ['Type', 'Nombre'],
            [
                ['Activités totales', $activities],
                ['Activités avec typologies', $activitiesWithTypologies]
            ]
        );

        if ($activities > 0) {
            $this->info("✅ Activités configurées");
        } else {
            $this->error("❌ Aucune activité trouvée");
        }
    }
}
