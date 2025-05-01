<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommonDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Create a few attachments for various entities
        $attachments = [
            [
                'id' => 1,
                'path' => 'attachments/2025/01',
                'name' => 'rapport_annuel_2024.pdf',
                'crypt' => hash('sha256', 'rapport_annuel_2024.pdf'),
                'thumbnail_path' => 'attachments/2025/01/thumbnails/rapport_annuel_2024.jpg',
                'size' => 2458921,
                'crypt_sha512' => hash('sha512', 'rapport_annuel_2024.pdf'),
                'type' => 'record',
                'creator_id' => 2,
                'created_at' => $now->subDays(60),
                'updated_at' => $now->subDays(60),
            ],
            [
                'id' => 2,
                'path' => 'attachments/2025/01',
                'name' => 'budget_previsionnel_2025.xlsx',
                'crypt' => hash('sha256', 'budget_previsionnel_2025.xlsx'),
                'thumbnail_path' => 'attachments/2025/01/thumbnails/budget_previsionnel_2025.jpg',
                'size' => 1245789,
                'crypt_sha512' => hash('sha512', 'budget_previsionnel_2025.xlsx'),
                'type' => 'record',
                'creator_id' => 2,
                'created_at' => $now->subDays(55),
                'updated_at' => $now->subDays(55),
            ],
            [
                'id' => 3,
                'path' => 'attachments/2025/02',
                'name' => 'note_service_2025-02.docx',
                'crypt' => hash('sha256', 'note_service_2025-02.docx'),
                'thumbnail_path' => 'attachments/2025/02/thumbnails/note_service_2025-02.jpg',
                'size' => 358912,
                'crypt_sha512' => hash('sha512', 'note_service_2025-02.docx'),
                'type' => 'mail',
                'creator_id' => 3,
                'created_at' => $now->subDays(45),
                'updated_at' => $now->subDays(45),
            ],
            [
                'id' => 4,
                'path' => 'attachments/2025/02',
                'name' => 'plan_batiment_principal.dwg',
                'crypt' => hash('sha256', 'plan_batiment_principal.dwg'),
                'thumbnail_path' => 'attachments/2025/02/thumbnails/plan_batiment_principal.jpg',
                'size' => 5842361,
                'crypt_sha512' => hash('sha512', 'plan_batiment_principal.dwg'),
                'type' => 'record',
                'creator_id' => 4,
                'created_at' => $now->subDays(40),
                'updated_at' => $now->subDays(40),
            ],
            [
                'id' => 5,
                'path' => 'attachments/2025/03',
                'name' => 'organigramme_2025.pdf',
                'crypt' => hash('sha256', 'organigramme_2025.pdf'),
                'thumbnail_path' => 'attachments/2025/03/thumbnails/organigramme_2025.jpg',
                'size' => 1248623,
                'crypt_sha512' => hash('sha512', 'organigramme_2025.pdf'),
                'type' => 'mail',
                'creator_id' => 3,
                'created_at' => $now->subDays(30),
                'updated_at' => $now->subDays(30),
            ],
            [
                'id' => 6,
                'path' => 'attachments/2025/03',
                'name' => 'proces_verbal_reunion_mars.pdf',
                'crypt' => hash('sha256', 'proces_verbal_reunion_mars.pdf'),
                'thumbnail_path' => 'attachments/2025/03/thumbnails/proces_verbal_reunion_mars.jpg',
                'size' => 856321,
                'crypt_sha512' => hash('sha512', 'proces_verbal_reunion_mars.pdf'),
                'type' => 'record',
                'creator_id' => 2,
                'created_at' => $now->subDays(25),
                'updated_at' => $now->subDays(25),
            ],
            [
                'id' => 7,
                'path' => 'attachments/2025/04',
                'name' => 'invitation_evenement_2025.pdf',
                'crypt' => hash('sha256', 'invitation_evenement_2025.pdf'),
                'thumbnail_path' => 'attachments/2025/04/thumbnails/invitation_evenement_2025.jpg',
                'size' => 458963,
                'crypt_sha512' => hash('sha512', 'invitation_evenement_2025.pdf'),
                'type' => 'bulletinboardevent',
                'creator_id' => 5,
                'created_at' => $now->subDays(15),
                'updated_at' => $now->subDays(15),
            ],
            [
                'id' => 8,
                'path' => 'attachments/2025/04',
                'name' => 'annonce_recrutement_2025.pdf',
                'crypt' => hash('sha256', 'annonce_recrutement_2025.pdf'),
                'thumbnail_path' => 'attachments/2025/04/thumbnails/annonce_recrutement_2025.jpg',
                'size' => 378954,
                'crypt_sha512' => hash('sha512', 'annonce_recrutement_2025.pdf'),
                'type' => 'bulletinboardpost',
                'creator_id' => 3,
                'created_at' => $now->subDays(10),
                'updated_at' => $now->subDays(10),
            ],
            [
                'id' => 9,
                'path' => 'attachments/2025/04',
                'name' => 'rapport_activite_q1_2025.pdf',
                'crypt' => hash('sha256', 'rapport_activite_q1_2025.pdf'),
                'thumbnail_path' => 'attachments/2025/04/thumbnails/rapport_activite_q1_2025.jpg',
                'size' => 2587456,
                'crypt_sha512' => hash('sha512', 'rapport_activite_q1_2025.pdf'),
                'type' => 'mail',
                'creator_id' => 2,
                'created_at' => $now->subDays(5),
                'updated_at' => $now->subDays(5),
            ],
            [
                'id' => 10,
                'path' => 'attachments/2025/04',
                'name' => 'convention_partenariat_2025.pdf',
                'crypt' => hash('sha256', 'convention_partenariat_2025.pdf'),
                'thumbnail_path' => 'attachments/2025/04/thumbnails/convention_partenariat_2025.jpg',
                'size' => 1845632,
                'crypt_sha512' => hash('sha512', 'convention_partenariat_2025.pdf'),
                'type' => 'record',
                'creator_id' => 1,
                'created_at' => $now->subDays(3),
                'updated_at' => $now->subDays(3),
            ],
        ];

        // Link attachments to records and mails
        $recordAttachments = [
            ['record_id' => 36, 'attachment_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['record_id' => 37, 'attachment_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['record_id' => 38, 'attachment_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['record_id' => 39, 'attachment_id' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['record_id' => 40, 'attachment_id' => 10, 'created_at' => $now, 'updated_at' => $now],
        ];

        $mailAttachments = [
            ['mail_id' => 1, 'attachment_id' => 3, 'added_by' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['mail_id' => 2, 'attachment_id' => 5, 'added_by' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['mail_id' => 3, 'attachment_id' => 9, 'added_by' => 2, 'created_at' => $now, 'updated_at' => $now],
        ];

        $eventAttachments = [
            ['id' => 1, 'event_id' => 1, 'attachment_id' => 7, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];

        $postAttachments = [
            ['id' => 1, 'post_id' => 3, 'attachment_id' => 8, 'created_by' => 3, 'created_at' => $now, 'updated_at' => $now],
        ];

        // Create some logs
        $logs = [];

        for ($i = 1; $i <= 50; $i++) {
            $userId = rand(1, 20);
            $date = Carbon::now()->subDays(rand(1, 90))->subHours(rand(1, 23))->subMinutes(rand(1, 59));

            $logs[] = [
                'id' => $i,
                'user_id' => $userId,
                'action' => $this->getLogAction($i),
                'description' => $this->getLogDescription($i, $userId),
                'ip_address' => '192.168.1.' . rand(2, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
                'created_at' => $date,
                'updated_at' => $date,
            ];
        }

        // Create document types
        $documentTypes = [
            [
                'id' => 1,
                'name' => 'Rapport',
                'description' => 'Document de synthèse sur un sujet spécifique',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Note',
                'description' => 'Document concis sur un sujet particulier',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Lettre',
                'description' => 'Correspondance officielle',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Procès-verbal',
                'description' => 'Compte-rendu officiel d\'une réunion',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Contrat',
                'description' => 'Document juridique établissant un accord',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insert the data
        DB::table('attachments')->insert($attachments);
        DB::table('record_attachment')->insert($recordAttachments);
        DB::table('mail_attachment')->insert($mailAttachments);
        DB::table('event_attachments')->insert($eventAttachments);
        DB::table('post_attachments')->insert($postAttachments);
        DB::table('logs')->insert($logs);
        DB::table('document_types')->insert($documentTypes);
    }

    private function getLogAction($i)
    {
        $actions = [
            'Connexion',
            'Déconnexion',
            'Création',
            'Modification',
            'Suppression',
            'Consultation',
            'Exportation',
            'Importation',
            'Archivage',
            'Réservation',
        ];

        return $actions[$i % 10];
    }

    private function getLogDescription($i, $userId)
    {
        $username = DB::table('users')->where('id', $userId)->value('name');

        $entityTypes = [
            'record',
            'mail',
            'user',
            'organisation',
            'container',
            'communication',
            'bulletin board',
            'setting',
            'term',
            'attachment',
        ];

        $entityType = $entityTypes[$i % 10];
        $entityId = $i + 10;

        $actions = [
            'Connexion' => "L'utilisateur $username s'est connecté au système",
            'Déconnexion' => "L'utilisateur $username s'est déconnecté du système",
            'Création' => "L'utilisateur $username a créé un(e) $entityType (ID: $entityId)",
            'Modification' => "L'utilisateur $username a modifié un(e) $entityType (ID: $entityId)",
            'Suppression' => "L'utilisateur $username a supprimé un(e) $entityType (ID: $entityId)",
            'Consultation' => "L'utilisateur $username a consulté un(e) $entityType (ID: $entityId)",
            'Exportation' => "L'utilisateur $username a exporté un(e) $entityType (ID: $entityId)",
            'Importation' => "L'utilisateur $username a importé un(e) $entityType (ID: $entityId)",
            'Archivage' => "L'utilisateur $username a archivé un(e) $entityType (ID: $entityId)",
            'Réservation' => "L'utilisateur $username a réservé un(e) $entityType (ID: $entityId)",
        ];

        $action = $this->getLogAction($i);
        return $actions[$action];
    }
}
