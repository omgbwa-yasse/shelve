<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MailSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Mail priorities
        $mailPriorities = [
            [
                'id' => 1,
                'name' => 'Urgent',
                'duration' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Haute',
                'duration' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Normale',
                'duration' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Basse',
                'duration' => 15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Mail actions
        $mailActions = [
            [
                'id' => 1,
                'name' => 'Pour information',
                'duration' => 0,
                'to_return' => false,
                'description' => 'Document transmis pour information',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Pour avis',
                'duration' => 7,
                'to_return' => true,
                'description' => 'Document transmis pour avis',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Pour validation',
                'duration' => 5,
                'to_return' => true,
                'description' => 'Document transmis pour validation',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Pour signature',
                'duration' => 3,
                'to_return' => true,
                'description' => 'Document transmis pour signature',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Pour action',
                'duration' => 10,
                'to_return' => true,
                'description' => 'Document transmis pour prise en charge',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Mail typologies
        $mailTypologies = [
            [
                'id' => 1,
                'code' => 'COUR',
                'name' => 'Courrier',
                'description' => 'Courrier standard',
                'activity_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'NOTE',
                'name' => 'Note interne',
                'description' => 'Note de service',
                'activity_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'MEMO',
                'name' => 'Mémorandum',
                'description' => 'Document d\'information',
                'activity_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'CONV',
                'name' => 'Convention',
                'description' => 'Document contractuel',
                'activity_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'code' => 'FACT',
                'name' => 'Facture',
                'description' => 'Document financier',
                'activity_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'code' => 'RAPP',
                'name' => 'Rapport',
                'description' => 'Document de synthèse',
                'activity_id' => 8,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Create mail containers
        $mailContainers = [
            [
                'id' => 1,
                'code' => 'MC-001',
                'name' => 'Classeur Courriers Entrants 2025',
                'type_id' => 5,
                'created_by' => 5,
                'creator_organisation_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'MC-002',
                'name' => 'Classeur Courriers Sortants 2025',
                'type_id' => 5,
                'created_by' => 5,
                'creator_organisation_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'MC-003',
                'name' => 'Classeur Notes Internes 2025',
                'type_id' => 5,
                'created_by' => 5,
                'creator_organisation_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Mail data
        $mails = [];
        $mailRelated = [];
        $mailArchives = [];

        // Create 30 mail entries
        for ($i = 1; $i <= 30; $i++) {
            $mailType = $i % 3 == 0 ? 'internal' : ($i % 3 == 1 ? 'incoming' : 'outgoing');
            $status = $i <= 20 ? 'transmitted' : ($i <= 25 ? 'in_progress' : 'draft');
            $isArchived = $i <= 15;

            // Determine sender and recipient based on mail type
            $senderUserId = null;
            $senderOrgId = null;
            $recipientUserId = null;
            $recipientOrgId = null;

            switch ($mailType) {
                case 'internal':
                    $senderUserId = rand(9, 14); // Producteurs
                    $senderOrgId = DB::table('users')->where('id', $senderUserId)->value('current_organisation_id');
                    $recipientUserId = rand(2, 4); // Admin orgs
                    $recipientOrgId = DB::table('users')->where('id', $recipientUserId)->value('current_organisation_id');
                    break;

                case 'incoming':
                    $senderUserId = null;
                    $senderOrgId = null;
                    $recipientUserId = rand(2, 8); // Admin orgs or archivistes
                    $recipientOrgId = DB::table('users')->where('id', $recipientUserId)->value('current_organisation_id');
                    break;

                case 'outgoing':
                    $senderUserId = rand(2, 8); // Admin orgs or archivistes
                    $senderOrgId = DB::table('users')->where('id', $senderUserId)->value('current_organisation_id');
                    $recipientUserId = null;
                    $recipientOrgId = null;
                    break;
            }

            $date = Carbon::now()->subDays(rand(1, 60));

            $mails[] = [
                'id' => $i,
                'code' => 'MAIL-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'name' => $this->getMailSubject($i, $mailType),
                'date' => $date,
                'description' => $this->getMailDescription($i, $mailType),
                'document_type' => rand(1, 10) > 8 ? 'copy' : 'original',
                'status' => $status,
                'priority_id' => rand(1, 4),
                'typology_id' => rand(1, 6),
                'action_id' => rand(1, 5),
                'sender_user_id' => $senderUserId,
                'sender_organisation_id' => $senderOrgId,
                'recipient_user_id' => $recipientUserId,
                'recipient_organisation_id' => $recipientOrgId,
                'mail_type' => $mailType,
                'is_archived' => $isArchived,
                'created_at' => $date,
                'updated_at' => $date,
            ];

            // Create related mail connections (for some mails)
            if ($i > 5 && $i % 4 == 0) {
                $mailRelated[] = [
                    'mail_id' => $i,
                    'mail_related_id' => $i - 4,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Create mail archives for archived mails
            if ($isArchived) {
                $containerId = $mailType == 'incoming' ? 1 : ($mailType == 'outgoing' ? 2 : 3);

                $mailArchives[] = [
                    'id' => $i,
                    'container_id' => $containerId,
                    'mail_id' => $i,
                    'archived_by' => 5, // Archivist
                    'document_type' => rand(1, 10) > 8 ? 'copy' : 'original',
                    'created_at' => $date->addDays(1),
                    'updated_at' => $date,
                ];
            }
        }

        // Insert the data
        DB::table('mail_priorities')->insert($mailPriorities);
        DB::table('mail_actions')->insert($mailActions);
        DB::table('mail_typologies')->insert($mailTypologies);
        DB::table('mail_containers')->insert($mailContainers);
        DB::table('mails')->insert($mails);
        DB::table('mail_related')->insert($mailRelated);
        DB::table('mail_archives')->insert($mailArchives);
    }

    private function getMailSubject($id, $type)
    {
        $internalSubjects = [
            'Note relative à la réorganisation du service',
            'Convocation réunion du comité de direction',
            'Note de service - Nouvelles procédures d\'archivage',
            'Mémo sur le budget prévisionnel',
            'Circulaire interne - Horaires d\'été',
            'Note d\'information - Mise à jour logicielle',
            'Compte-rendu réunion du personnel',
            'Proposition d\'amélioration des processus',
            'Demande de validation du plan d\'action',
            'Note sur les congés annuels',
        ];

        $incomingSubjects = [
            'Demande d\'accès aux archives historiques',
            'Candidature spontanée',
            'Réclamation usager',
            'Invitation à la conférence annuelle',
            'Demande de partenariat',
            'Proposition commerciale',
            'Demande de renseignements',
            'Notification administrative',
            'Convocation officielle',
            'Notification de décision',
        ];

        $outgoingSubjects = [
            'Réponse à la demande d\'accès du 15/03/2025',
            'Notification de versement d\'archives',
            'Invitation à l\'inauguration',
            'Demande de financement',
            'Rapport d\'activité annuel',
            'Convocation à l\'assemblée générale',
            'Confirmation de participation',
            'Demande d\'autorisation',
            'Envoi des documents sollicités',
            'Attestation administrative',
        ];

        $index = ($id - 1) % 10;

        switch ($type) {
            case 'internal':
                return $internalSubjects[$index];
            case 'incoming':
                return $incomingSubjects[$index];
            case 'outgoing':
                return $outgoingSubjects[$index];
            default:
                return 'Courrier #' . $id;
        }
    }

    private function getMailDescription($id, $type)
    {
        $descriptions = [
            'internal' => 'Document interne relatif à l\'organisation du service. À diffuser à l\'ensemble du personnel concerné.',
            'incoming' => 'Courrier reçu nécessitant un traitement dans les délais impartis. À enregistrer dans le système de suivi.',
            'outgoing' => 'Courrier émis en réponse à une demande antérieure. Une copie doit être conservée dans les archives du service.',
        ];

        return $descriptions[$type] . ' (Document #' . $id . ')';
    }
}
