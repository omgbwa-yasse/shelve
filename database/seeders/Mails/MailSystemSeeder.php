<?php

namespace Database\Seeders\Mails;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MailTypology;
use App\Models\MailPriority;
use App\Models\MailAction;
use App\Models\Activity;

class MailSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedMailPriorities();
        $this->seedMailActions();
        $this->seedMailTypologies();
    }

    /**
     * CrÃ©er les prioritÃ©s de courrier
     */
    private function seedMailPriorities()
    {
        $priorities = [
            [
                'name' => 'TrÃ¨s urgent',
                'duration' => 1 // 1 jour
            ],
            [
                'name' => 'Urgent',
                'duration' => 3 // 3 jours
            ],
            [
                'name' => 'Normal',
                'duration' => 7 // 7 jours
            ],
            [
                'name' => 'Faible',
                'duration' => 15 // 15 jours
            ],
            [
                'name' => 'Informationnel',
                'duration' => 30 // 30 jours
            ]
        ];

        foreach ($priorities as $priority) {
            MailPriority::firstOrCreate(
                ['name' => $priority['name']],
                $priority
            );
        }

        $this->command->info('PrioritÃ©s de courrier crÃ©Ã©es: ' . count($priorities));
    }

    /**
     * CrÃ©er les actions de courrier
     */
    private function seedMailActions()
    {
        $actions = [
            [
                'name' => 'Pour information',
                'duration' => 0,
                'to_return' => false,
                'description' => 'Courrier transmis Ã  titre informatif uniquement'
            ],
            [
                'name' => 'Pour avis',
                'duration' => 5,
                'to_return' => true,
                'description' => 'Demande d\'avis ou de consultation'
            ],
            [
                'name' => 'Pour dÃ©cision',
                'duration' => 7,
                'to_return' => true,
                'description' => 'NÃ©cessite une prise de dÃ©cision'
            ],
            [
                'name' => 'Pour signature',
                'duration' => 3,
                'to_return' => true,
                'description' => 'Document Ã  signer'
            ],
            [
                'name' => 'Pour validation',
                'duration' => 5,
                'to_return' => true,
                'description' => 'Document Ã  valider ou approuver'
            ],
            [
                'name' => 'Pour traitement',
                'duration' => 10,
                'to_return' => true,
                'description' => 'Dossier nÃ©cessitant un traitement complet'
            ],
            [
                'name' => 'Pour suivi',
                'duration' => 15,
                'to_return' => false,
                'description' => 'Dossier Ã  suivre rÃ©guliÃ¨rement'
            ],
            [
                'name' => 'Pour archivage',
                'duration' => 0,
                'to_return' => false,
                'description' => 'Document Ã  archiver'
            ]
        ];

        foreach ($actions as $action) {
            MailAction::firstOrCreate(
                ['name' => $action['name']],
                $action
            );
        }

        $this->command->info('Actions de courrier crÃ©Ã©es: ' . count($actions));
    }

    /**
     * CrÃ©er les typologies de courrier
     */
    private function seedMailTypologies()
    {
        // VÃ©rifier s'il y a des activitÃ©s, sinon crÃ©er une activitÃ© par dÃ©faut
        $defaultActivity = Activity::first();
        if (!$defaultActivity) {
            $defaultActivity = Activity::create([
                'code' => 'ADM',
                'name' => 'Administration gÃ©nÃ©rale',
                'observation' => 'ActivitÃ© par dÃ©faut pour les courriers'
            ]);
        }

        $typologies = [
            // Correspondance administrative
            [
                'code' => 'CORR',
                'name' => 'Correspondance gÃ©nÃ©rale',
                'description' => 'Courrier de correspondance gÃ©nÃ©rale',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'INFO',
                'name' => 'Information',
                'description' => 'Courrier d\'information',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'CONV',
                'name' => 'Convocation',
                'description' => 'Convocation Ã  une rÃ©union ou Ã©vÃ©nement',
                'activity_id' => $defaultActivity->id
            ],

            // Demandes et rÃ©clamations
            [
                'code' => 'DSTG',
                'name' => 'Demande de stage',
                'description' => 'Demande de stage ou convention de stage',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'DAID',
                'name' => 'Demande d\'aide',
                'description' => 'Demande d\'aide financiÃ¨re ou matÃ©rielle',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'DFOR',
                'name' => 'Demande de formation',
                'description' => 'Demande de formation professionnelle',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'RECL',
                'name' => 'RÃ©clamation',
                'description' => 'RÃ©clamation ou plainte',
                'activity_id' => $defaultActivity->id
            ],

            // Documents officiels
            [
                'code' => 'CERT',
                'name' => 'Certificat',
                'description' => 'Certificat ou attestation officielle',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'DECL',
                'name' => 'DÃ©claration',
                'description' => 'DÃ©claration officielle',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'PERM',
                'name' => 'Autorisation/Permis',
                'description' => 'Demande d\'autorisation ou de permis',
                'activity_id' => $defaultActivity->id
            ],

            // Aspects financiers
            [
                'code' => 'CRED',
                'name' => 'CrÃ©dit/Financement',
                'description' => 'Demande de crÃ©dit ou financement',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'FACT',
                'name' => 'Facture',
                'description' => 'Facture ou document comptable',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'BUDG',
                'name' => 'Budget',
                'description' => 'Document budgÃ©taire',
                'activity_id' => $defaultActivity->id
            ],

            // Ressources humaines
            [
                'code' => 'CAND',
                'name' => 'Candidature',
                'description' => 'Candidature spontanÃ©e ou rÃ©ponse Ã  offre',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'CONT',
                'name' => 'Contrat',
                'description' => 'Contrat de travail ou autre',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'EVAL',
                'name' => 'Ã‰valuation',
                'description' => 'Ã‰valuation de personnel ou de service',
                'activity_id' => $defaultActivity->id
            ],

            // Juridique et contentieux
            [
                'code' => 'JUST',
                'name' => 'Justice/Contentieux',
                'description' => 'Courrier juridique ou contentieux',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'MISE',
                'name' => 'Mise en demeure',
                'description' => 'Mise en demeure ou sommation',
                'activity_id' => $defaultActivity->id
            ],

            // Communication et relations publiques
            [
                'code' => 'COMM',
                'name' => 'Communication',
                'description' => 'Document de communication externe',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'PRES',
                'name' => 'Presse',
                'description' => 'CommuniquÃ© de presse ou relation presse',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'PART',
                'name' => 'Partenariat',
                'description' => 'Proposition ou convention de partenariat',
                'activity_id' => $defaultActivity->id
            ],

            // Technique et maintenance
            [
                'code' => 'TECH',
                'name' => 'Technique',
                'description' => 'Document technique ou spÃ©cification',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'MAIN',
                'name' => 'Maintenance',
                'description' => 'Demande de maintenance ou rÃ©paration',
                'activity_id' => $defaultActivity->id
            ],

            // Divers
            [
                'code' => 'INVT',
                'name' => 'Invitation',
                'description' => 'Invitation Ã  un Ã©vÃ©nement',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'RAPP',
                'name' => 'Rapport',
                'description' => 'Rapport d\'activitÃ© ou d\'Ã©tude',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'DIRS',
                'name' => 'Divers',
                'description' => 'Courrier divers non classÃ©',
                'activity_id' => $defaultActivity->id
            ]
        ];

        foreach ($typologies as $typology) {
            MailTypology::firstOrCreate(
                ['code' => $typology['code']],
                $typology
            );
        }

        $this->command->info('Typologies de courrier crÃ©Ã©es: ' . count($typologies));
    }
}

