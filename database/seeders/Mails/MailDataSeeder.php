<?php

namespace Database\Seeders\Mails;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Mail;
use App\Models\MailHistory;
use App\Models\MailContainer;
use App\Models\Batch;
use App\Models\BatchTransaction;
use App\Models\User;
use App\Models\Organisation;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailAction;

class MailDataSeeder extends Seeder
{
    /**
     * Seed test data for the Courriers (Mails) module.
     * Creates mails of all types, transactions, history, batches and metrics.
     * Idempotent: uses firstOrCreate/updateOrCreate.
     */
    public function run(): void
    {
        $this->command->info('📬 Seeding Mails module test data...');

        $user = User::first();
        $users = User::take(4)->get();
        if ($users->isEmpty()) {
            $this->command->warn('⚠️  No users found. Run SuperAdminSeeder first.');
            return;
        }

        $org = Organisation::first();
        $orgs = Organisation::take(3)->get();
        if (!$org) {
            $this->command->warn('⚠️  No organisations found. Run OrganisationSeeder first.');
            return;
        }

        $priority = MailPriority::first();
        $typology = MailTypology::first();
        $action = MailAction::first();

        // --- 1. Mail Containers ---
        $container1 = MailContainer::firstOrCreate(
            ['code' => 'MC-2026-001'],
            ['name' => 'Classeur Courriers Entrants 2026', 'property_id' => null, 'created_by' => $user->id, 'creator_organisation_id' => $org->id]
        );
        $container2 = MailContainer::firstOrCreate(
            ['code' => 'MC-2026-002'],
            ['name' => 'Classeur Courriers Sortants 2026', 'property_id' => null, 'created_by' => $user->id, 'creator_organisation_id' => $org->id]
        );

        // --- 2. Internal Mails ---
        $internalMails = [
            ['code' => 'INT-2026-001', 'name' => 'Note de service - Horaires de travail', 'status' => 'completed', 'mail_type' => Mail::TYPE_INTERNAL, 'description' => 'Modification des horaires de travail à compter du 1er mars 2026'],
            ['code' => 'INT-2026-002', 'name' => 'Demande de congé annuel', 'status' => 'in_progress', 'mail_type' => Mail::TYPE_INTERNAL, 'description' => 'Demande de congé du 15 au 30 mars 2026'],
            ['code' => 'INT-2026-003', 'name' => 'Convocation réunion de direction', 'status' => 'transmitted', 'mail_type' => Mail::TYPE_INTERNAL, 'description' => 'Réunion mensuelle de direction prévue le 28 février 2026'],
            ['code' => 'INT-2026-004', 'name' => 'Note interne - Budget prévisionnel', 'status' => 'draft', 'mail_type' => Mail::TYPE_INTERNAL, 'description' => 'Proposition de budget prévisionnel pour l\'exercice 2026-2027'],
            ['code' => 'INT-2026-005', 'name' => 'Rapport d\'activité mensuel', 'status' => 'pending_review', 'mail_type' => Mail::TYPE_INTERNAL, 'description' => 'Rapport d\'activité du mois de janvier 2026'],
        ];

        // --- 3. Incoming Mails ---
        $incomingMails = [
            ['code' => 'IN-2026-001', 'name' => 'Lettre du Ministère de la Culture', 'status' => 'completed', 'mail_type' => Mail::TYPE_INCOMING, 'description' => 'Instructions relatives à la numérisation des archives nationales'],
            ['code' => 'IN-2026-002', 'name' => 'Demande de consultation d\'archives', 'status' => 'in_progress', 'mail_type' => Mail::TYPE_INCOMING, 'description' => 'Demande d\'accès aux dossiers de la période coloniale'],
            ['code' => 'IN-2026-003', 'name' => 'Facture fournisseur matériel', 'status' => 'pending_approval', 'mail_type' => Mail::TYPE_INCOMING, 'description' => 'Facture pour l\'achat de matériel de conservation'],
            ['code' => 'IN-2026-004', 'name' => 'Courrier du Tribunal Administratif', 'status' => 'approved', 'mail_type' => Mail::TYPE_INCOMING, 'description' => 'Notification de décision concernant un litige foncier'],
            ['code' => 'IN-2026-005', 'name' => 'Demande de versement d\'archives', 'status' => 'in_progress', 'mail_type' => Mail::TYPE_INCOMING, 'description' => 'Proposition de versement d\'archives du service des impôts'],
        ];

        // --- 4. Outgoing Mails ---
        $outgoingMails = [
            ['code' => 'OUT-2026-001', 'name' => 'Réponse au Ministère de la Culture', 'status' => 'completed', 'mail_type' => Mail::TYPE_OUTGOING, 'description' => 'Accusé de réception et plan d\'action pour la numérisation'],
            ['code' => 'OUT-2026-002', 'name' => 'Attestation de dépôt d\'archives', 'status' => 'transmitted', 'mail_type' => Mail::TYPE_OUTGOING, 'description' => 'Attestation de dépôt pour le service des impôts'],
            ['code' => 'OUT-2026-003', 'name' => 'Demande de financement - Projet numérique', 'status' => 'pending_review', 'mail_type' => Mail::TYPE_OUTGOING, 'description' => 'Demande de subvention pour le projet de numérisation'],
            ['code' => 'OUT-2026-004', 'name' => 'Invitation colloque archivistique', 'status' => 'completed', 'mail_type' => Mail::TYPE_OUTGOING, 'description' => 'Invitation au colloque international des archivistes'],
            ['code' => 'OUT-2026-005', 'name' => 'Notification de rejet de demande', 'status' => 'draft', 'mail_type' => Mail::TYPE_OUTGOING, 'description' => 'Notification de rejet d\'une demande de communication non autorisée'],
        ];

        $allMailData = array_merge($internalMails, $incomingMails, $outgoingMails);
        $createdMails = [];

        foreach ($allMailData as $i => $mailData) {
            $senderUser = $users[$i % $users->count()];
            $recipientUser = $users[($i + 1) % $users->count()];
            $senderOrg = $orgs[$i % $orgs->count()];
            $recipientOrg = $orgs[($i + 1) % $orgs->count()];

            $mail = Mail::firstOrCreate(
                ['code' => $mailData['code']],
                array_merge($mailData, [
                    'date' => now()->subDays(rand(1, 60)),
                    'priority_id' => $priority?->id,
                    'typology_id' => $typology?->id,
                    'action_id' => $action?->id,
                    'sender_user_id' => $senderUser->id,
                    'sender_organisation_id' => $senderOrg->id,
                    'sender_type' => 'internal',
                    'recipient_user_id' => $recipientUser->id,
                    'recipient_organisation_id' => $recipientOrg->id,
                    'recipient_type' => 'internal',
                    'assigned_to' => $recipientUser->id,
                    'assigned_organisation_id' => $recipientOrg->id,
                    'assigned_at' => now()->subDays(rand(1, 30)),
                    'deadline' => now()->addDays(rand(5, 30)),
                ])
            );
            $createdMails[] = $mail;
        }

        // --- 5. Mail History (audit trail) ---
        $historyActions = ['created', 'status_changed', 'assigned', 'transmitted', 'archived', 'commented'];
        foreach (array_slice($createdMails, 0, 10) as $mail) {
            foreach (array_slice($historyActions, 0, rand(2, 4)) as $historyAction) {
                MailHistory::firstOrCreate(
                    ['mail_id' => $mail->id, 'action' => $historyAction, 'user_id' => $user->id],
                    [
                        'description' => "Action '$historyAction' effectuée sur le courrier {$mail->code}",
                        'field_changed' => $historyAction === 'status_changed' ? 'status' : null,
                        'old_value' => $historyAction === 'status_changed' ? json_encode('draft') : null,
                        'new_value' => $historyAction === 'status_changed' ? json_encode('in_progress') : null,
                        'ip_address' => '127.0.0.1',
                    ]
                );
            }
        }

        // --- 6. Batches ---
        $batch1 = Batch::firstOrCreate(
            ['code' => 'BT-26-001'],
            ['name' => 'Lot courriers entrants - Semaine 8', 'organisation_holder_id' => $org->id]
        );
        $batch2 = Batch::firstOrCreate(
            ['code' => 'BT-26-002'],
            ['name' => 'Lot courriers sortants - Février 2026', 'organisation_holder_id' => $org->id]
        );
        $batch3 = Batch::firstOrCreate(
            ['code' => 'BT-26-003'],
            ['name' => 'Lot courriers internes - DRH', 'organisation_holder_id' => $orgs->count() > 1 ? $orgs[1]->id : $org->id]
        );

        // Link mails to batches
        foreach (array_slice($createdMails, 5, 5) as $mail) {
            DB::table('batch_mail')->updateOrInsert(
                ['batch_id' => $batch1->id, 'mail_id' => $mail->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // --- 7. Batch Transactions ---
        if ($orgs->count() >= 2) {
            BatchTransaction::firstOrCreate(
                ['batch_id' => $batch1->id, 'organisation_send_id' => $orgs[0]->id],
                ['organisation_received_id' => $orgs[1]->id]
            );
            BatchTransaction::firstOrCreate(
                ['batch_id' => $batch2->id, 'organisation_send_id' => $orgs[1]->id],
                ['organisation_received_id' => $orgs[0]->id]
            );
        }

        // --- 8. Mail Archives ---
        foreach (array_slice($createdMails, 0, 4) as $mail) {
            DB::table('mail_archives')->updateOrInsert(
                ['mail_id' => $mail->id, 'container_id' => $container1->id],
                ['archived_by' => $user->id, 'document_type' => 'original', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('✅ Mails module: ' . count($createdMails) . ' mails, history, batches seeded.');
    }
}
