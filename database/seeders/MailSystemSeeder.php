<?php

namespace Database\Seeders;

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
     * Créer les priorités de courrier
     */
    private function seedMailPriorities()
    {
        $priorities = [
            [
                'name' => 'Très urgent',
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

        $this->command->info('Priorités de courrier créées: ' . count($priorities));
    }

    /**
     * Créer les actions de courrier
     */
    private function seedMailActions()
    {
        $actions = [
            [
                'name' => 'Pour information',
                'duration' => 0,
                'to_return' => false,
                'description' => 'Courrier transmis à titre informatif uniquement'
            ],
            [
                'name' => 'Pour avis',
                'duration' => 5,
                'to_return' => true,
                'description' => 'Demande d\'avis ou de consultation'
            ],
            [
                'name' => 'Pour décision',
                'duration' => 7,
                'to_return' => true,
                'description' => 'Nécessite une prise de décision'
            ],
            [
                'name' => 'Pour signature',
                'duration' => 3,
                'to_return' => true,
                'description' => 'Document à signer'
            ],
            [
                'name' => 'Pour validation',
                'duration' => 5,
                'to_return' => true,
                'description' => 'Document à valider ou approuver'
            ],
            [
                'name' => 'Pour traitement',
                'duration' => 10,
                'to_return' => true,
                'description' => 'Dossier nécessitant un traitement complet'
            ],
            [
                'name' => 'Pour suivi',
                'duration' => 15,
                'to_return' => false,
                'description' => 'Dossier à suivre régulièrement'
            ],
            [
                'name' => 'Pour archivage',
                'duration' => 0,
                'to_return' => false,
                'description' => 'Document à archiver'
            ]
        ];

        foreach ($actions as $action) {
            MailAction::firstOrCreate(
                ['name' => $action['name']],
                $action
            );
        }

        $this->command->info('Actions de courrier créées: ' . count($actions));
    }

    /**
     * Créer les typologies de courrier
     */
    private function seedMailTypologies()
    {
        // Vérifier s'il y a des activités, sinon créer une activité par défaut
        $defaultActivity = Activity::first();
        if (!$defaultActivity) {
            $defaultActivity = Activity::create([
                'code' => 'ADM',
                'name' => 'Administration générale',
                'observation' => 'Activité par défaut pour les courriers'
            ]);
        }

        $typologies = [
            // Correspondance administrative
            [
                'code' => 'CORR',
                'name' => 'Correspondance générale',
                'description' => 'Courrier de correspondance générale',
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
                'description' => 'Convocation à une réunion ou événement',
                'activity_id' => $defaultActivity->id
            ],

            // Demandes et réclamations
            [
                'code' => 'DSTG',
                'name' => 'Demande de stage',
                'description' => 'Demande de stage ou convention de stage',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'DAID',
                'name' => 'Demande d\'aide',
                'description' => 'Demande d\'aide financière ou matérielle',
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
                'name' => 'Réclamation',
                'description' => 'Réclamation ou plainte',
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
                'name' => 'Déclaration',
                'description' => 'Déclaration officielle',
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
                'name' => 'Crédit/Financement',
                'description' => 'Demande de crédit ou financement',
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
                'description' => 'Document budgétaire',
                'activity_id' => $defaultActivity->id
            ],

            // Ressources humaines
            [
                'code' => 'CAND',
                'name' => 'Candidature',
                'description' => 'Candidature spontanée ou réponse à offre',
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
                'name' => 'Évaluation',
                'description' => 'Évaluation de personnel ou de service',
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
                'description' => 'Communiqué de presse ou relation presse',
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
                'description' => 'Document technique ou spécification',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'MAIN',
                'name' => 'Maintenance',
                'description' => 'Demande de maintenance ou réparation',
                'activity_id' => $defaultActivity->id
            ],

            // Divers
            [
                'code' => 'INVT',
                'name' => 'Invitation',
                'description' => 'Invitation à un événement',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'RAPP',
                'name' => 'Rapport',
                'description' => 'Rapport d\'activité ou d\'étude',
                'activity_id' => $defaultActivity->id
            ],
            [
                'code' => 'DIRS',
                'name' => 'Divers',
                'description' => 'Courrier divers non classé',
                'activity_id' => $defaultActivity->id
            ]
        ];

        foreach ($typologies as $typology) {
            MailTypology::firstOrCreate(
                ['code' => $typology['code']],
                $typology
            );
        }

        $this->command->info('Typologies de courrier créées: ' . count($typologies));
    }
}
