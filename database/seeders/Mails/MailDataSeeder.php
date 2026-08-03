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
use App\Enums\MailStatusEnum;

/**
 * Données de démonstration du module Courrier, calées sur le circuit « zéro papier ».
 *
 * Chaque courrier illustre une étape précise du circuit, de sorte que la démo montre
 * l'enchaînement complet et que les boutons d'action apparaissent au bon endroit :
 *
 *  ENTRANT   : déposé à l'accueil → à coter par le DG → coté vers une direction
 *              → réception validée.
 *  SORTANT   : brouillon d'un agent → soumis au visa N+1/DG → signé par le DG
 *              (ou rejeté).
 *
 * Idempotent : firstOrCreate/updateOrInsert.
 */
class MailDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding des données de démonstration du module Courrier...');

        // --- Acteurs du circuit (créés par RoleHierarchySeeder) ---
        $dgUser      = User::where('email', 'dg@example.com')->first();
        $secretariat = User::where('email', 'secretariat@example.com')->first();
        $accueil     = User::where('email', 'accueil@example.com')->first();
        $dirDsi      = User::where('email', 'dir.dsi@example.com')->first();
        $agentDsi    = User::where('email', 'agent.dsi@example.com')->first();
        $dirDrh      = User::where('email', 'dir.drh@example.com')->first();
        $agentDrh    = User::where('email', 'agent.drh@example.com')->first();
        $dirDag      = User::where('email', 'dir.dag@example.com')->first();

        if (!$dgUser || !$dirDsi || !$dirDrh) {
            $this->command->warn('Utilisateurs de démonstration absents : lancez RoleHierarchySeeder avant.');
            return;
        }

        // --- Organisations ---
        $dg  = Organisation::where('code', 'DG')->withCount('children')->orderByDesc('children_count')->first();
        $dsi = Organisation::where('code', 'DSI')->first();
        $drh = Organisation::where('code', 'DRH')->first();
        $dag = Organisation::where('code', 'DAG')->first();

        if (!$dg || !$dsi || !$drh || !$dag) {
            $this->command->warn('Organisations absentes : lancez OrganisationSeeder avant.');
            return;
        }

        // --- Référentiels ---
        $priority     = MailPriority::first();
        $typoCourrier = MailTypology::where('code', 'CORR')->first() ?? MailTypology::first();
        $typoNote     = MailTypology::where('code', 'NOTE')->first() ?? $typoCourrier;
        $instrSuite   = MailAction::where('name', 'Donner suite')->first();
        $instrClasser = MailAction::where('name', 'Classer')->first();
        $actionInfo   = MailAction::where('name', 'Pour information')->first() ?? MailAction::first();

        $createdMails = [];

        // =====================================================================
        // 1. COURRIER ENTRANT — déposé à l'accueil, EN ATTENTE DE COTATION DG
        //    (aucune direction affectée → le DG voit le bouton « Coter »)
        // =====================================================================
        $createdMails[] = $in1 = Mail::firstOrCreate(
            ['code' => 'IN-2026-001'],
            [
                'name' => 'Lettre du Ministère de la Culture',
                'description' => "Instructions relatives à la numérisation des archives nationales. Déposée à l'accueil, en attente de cotation par le Directeur Général.",
                'date' => now()->subDays(2),
                'document_type' => 'original',
                'status' => MailStatusEnum::TRANSMITTED,
                'mail_type' => Mail::TYPE_INCOMING,
                'priority_id' => $priority?->id,
                'typology_id' => $typoCourrier?->id,
                'sender_type' => 'external_organization',
                'recipient_organisation_id' => $dg->id,
                'recipient_user_id' => $secretariat?->id,
                'recipient_type' => 'organisation',
                'deadline' => now()->addDays(10),
            ]
        );

        // =====================================================================
        // 2. COURRIER ENTRANT — COTÉ par le DG vers la DRH avec instruction
        //    (→ le directeur DRH voit le bouton « Valider la réception »)
        // =====================================================================
        $createdMails[] = $in2 = Mail::firstOrCreate(
            ['code' => 'IN-2026-002'],
            [
                'name' => 'Demande de stage - Université de Yaoundé',
                'description' => 'Demande de stage académique adressée à la structure. Cotée par le DG à la DRH pour suite à donner.',
                'date' => now()->subDays(5),
                'document_type' => 'original',
                'status' => MailStatusEnum::IN_PROGRESS,
                'mail_type' => Mail::TYPE_INCOMING,
                'priority_id' => $priority?->id,
                'typology_id' => $typoCourrier?->id,
                'action_id' => $instrSuite?->id,
                'sender_type' => 'external_contact',
                'recipient_organisation_id' => $dg->id,
                'recipient_type' => 'organisation',
                'assigned_organisation_id' => $drh->id,
                'assigned_at' => now()->subDays(4),
                'deadline' => now()->addDays(5),
            ]
        );

        // =====================================================================
        // 3. COURRIER ENTRANT — réception VALIDÉE par la direction (circuit terminé)
        // =====================================================================
        $createdMails[] = $in3 = Mail::firstOrCreate(
            ['code' => 'IN-2026-003'],
            [
                'name' => 'Facture fournisseur - Matériel informatique',
                'description' => 'Facture pour l\'achat de matériel informatique. Cotée à la DSI, réception validée par le directeur.',
                'date' => now()->subDays(20),
                'document_type' => 'original',
                'status' => MailStatusEnum::COMPLETED,
                'mail_type' => Mail::TYPE_INCOMING,
                'priority_id' => $priority?->id,
                'typology_id' => $typoCourrier?->id,
                'action_id' => $instrClasser?->id,
                'sender_type' => 'external_organization',
                'recipient_organisation_id' => $dg->id,
                'recipient_type' => 'organisation',
                'assigned_organisation_id' => $dsi->id,
                'assigned_to' => $dirDsi->id,
                'assigned_at' => now()->subDays(18),
                'processed_at' => now()->subDays(15),
            ]
        );

        // =====================================================================
        // 4. COURRIER SORTANT — BROUILLON d'un agent
        //    (→ l'agent voit le bouton « Soumettre pour validation »)
        // =====================================================================
        $createdMails[] = $out1 = Mail::firstOrCreate(
            ['code' => 'OUT-2026-001'],
            [
                'name' => 'Demande de devis - Serveurs de sauvegarde',
                'description' => 'Projet de courrier rédigé par un agent de la DSI, en attente de soumission au visa hiérarchique.',
                'date' => now()->subDays(1),
                'document_type' => 'original',
                'status' => MailStatusEnum::DRAFT,
                'mail_type' => Mail::TYPE_OUTGOING,
                'priority_id' => $priority?->id,
                'typology_id' => $typoCourrier?->id,
                'action_id' => $actionInfo?->id,
                'sender_user_id' => $agentDsi?->id,
                'sender_organisation_id' => $dsi->id,
                'sender_type' => 'user',
                'recipient_type' => 'external_organization',
            ]
        );

        // =====================================================================
        // 5. COURRIER SORTANT — SOUMIS au visa, en attente de signature du DG
        //    (→ le DG voit « Signer et transmettre » / « Rejeter »)
        // =====================================================================
        $createdMails[] = $out2 = Mail::firstOrCreate(
            ['code' => 'OUT-2026-002'],
            [
                'name' => 'Réponse au Ministère de la Culture',
                'description' => 'Accusé de réception et plan d\'action pour la numérisation des archives.',
                'date' => now()->subDays(3),
                'document_type' => 'original',
                'status' => MailStatusEnum::PENDING_APPROVAL,
                'mail_type' => Mail::TYPE_OUTGOING,
                'priority_id' => $priority?->id,
                'typology_id' => $typoCourrier?->id,
                'action_id' => $actionInfo?->id,
                'sender_user_id' => $agentDrh?->id,
                'sender_organisation_id' => $drh->id,
                'sender_type' => 'user',
                'recipient_type' => 'external_organization',
                'dg_signature_status' => 'pending',
                'explanatory_note' => "Note explicative : le Ministère demande un plan d'action sous 15 jours. Le projet de réponse reprend les engagements validés en réunion de direction et propose un calendrier en trois phases.",
                'deadline' => now()->addDays(7),
            ]
        );

        // =====================================================================
        // 6. COURRIER SORTANT — SIGNÉ par le DG (sorti de la structure)
        // =====================================================================
        $createdMails[] = $out3 = Mail::firstOrCreate(
            ['code' => 'OUT-2026-003'],
            [
                'name' => 'Attestation de dépôt d\'archives',
                'description' => 'Attestation de dépôt délivrée à un service partenaire.',
                'date' => now()->subDays(12),
                'document_type' => 'original',
                'status' => MailStatusEnum::TRANSMITTED,
                'mail_type' => Mail::TYPE_OUTGOING,
                'priority_id' => $priority?->id,
                'typology_id' => $typoCourrier?->id,
                'action_id' => $actionInfo?->id,
                'sender_user_id' => $dirDag?->id,
                'sender_organisation_id' => $dag->id,
                'sender_type' => 'user',
                'recipient_type' => 'external_organization',
                'dg_signature_status' => 'signed',
                'dg_signed_by' => $dgUser->id,
                'dg_signed_at' => now()->subDays(10),
                'dg_signature_note' => 'Bon pour envoi.',
                'explanatory_note' => 'Attestation conforme au modèle en vigueur.',
                'processed_at' => now()->subDays(10),
            ]
        );

        // =====================================================================
        // 7. COURRIER SORTANT — REJETÉ par le DG (retour à l'initiateur)
        // =====================================================================
        $createdMails[] = $out4 = Mail::firstOrCreate(
            ['code' => 'OUT-2026-004'],
            [
                'name' => 'Demande de financement - Projet numérique',
                'description' => 'Demande de subvention pour le projet de numérisation.',
                'date' => now()->subDays(8),
                'document_type' => 'original',
                'status' => MailStatusEnum::REJECTED,
                'mail_type' => Mail::TYPE_OUTGOING,
                'priority_id' => $priority?->id,
                'typology_id' => $typoCourrier?->id,
                'action_id' => $actionInfo?->id,
                'sender_user_id' => $agentDsi?->id,
                'sender_organisation_id' => $dsi->id,
                'sender_type' => 'user',
                'recipient_type' => 'external_organization',
                'dg_signature_status' => 'rejected',
                'dg_signed_by' => $dgUser->id,
                'dg_signed_at' => now()->subDays(6),
                'dg_signature_note' => 'À revoir : chiffrage à préciser et calendrier à joindre.',
                'explanatory_note' => 'Demande de subvention au titre du fonds de modernisation.',
            ]
        );

        // =====================================================================
        // 8. COURRIER INTERNE — note de service (typologie « Note de service »)
        // =====================================================================
        $createdMails[] = $int1 = Mail::firstOrCreate(
            ['code' => 'INT-2026-001'],
            [
                'name' => 'Note de service - Horaires de travail',
                'description' => 'Modification des horaires de travail à compter du 1er mars 2026.',
                'date' => now()->subDays(30),
                'document_type' => 'original',
                'status' => MailStatusEnum::COMPLETED,
                'mail_type' => Mail::TYPE_INTERNAL,
                'priority_id' => $priority?->id,
                'typology_id' => $typoNote?->id,
                'action_id' => $actionInfo?->id,
                'sender_user_id' => $dgUser->id,
                'sender_organisation_id' => $dg->id,
                'sender_type' => 'user',
                'recipient_organisation_id' => $drh->id,
                'recipient_user_id' => $dirDrh->id,
                'recipient_type' => 'organisation',
                'processed_at' => now()->subDays(29),
            ]
        );

        // =====================================================================
        // 9. COURRIER ENTRANT — coté à PLUSIEURS directions, réponses partielles
        //    (→ le DG suit chaque direction individuellement, la DSI a répondu,
        //       la DRH et la DAG sont encore en attente)
        // =====================================================================
        $createdMails[] = $in4 = Mail::firstOrCreate(
            ['code' => 'IN-2026-004'],
            [
                'name' => 'Circulaire interministérielle - Dématérialisation des procédures',
                'description' => 'Circulaire concernant plusieurs directions : volet technique (DSI), volet ressources humaines (DRH) et volet logistique (DAG).',
                'date' => now()->subDays(6),
                'document_type' => 'original',
                'status' => MailStatusEnum::IN_PROGRESS,
                'mail_type' => Mail::TYPE_INCOMING,
                'priority_id' => $priority?->id,
                'typology_id' => $typoCourrier?->id,
                'action_id' => $instrSuite?->id,
                'sender_type' => 'external_organization',
                'recipient_organisation_id' => $dg->id,
                'recipient_type' => 'organisation',
                'assigned_at' => now()->subDays(5),
                // Échéance dépassée : alimente le bloc « Délais dépassés » du tableau de bord.
                'deadline' => now()->subDays(2),
            ]
        );

        if ($in4->cotations()->doesntExist()) {
            $activites = fn ($org, $motif) => $org
                ? optional($org->activities()->where('name', 'like', "%$motif%")->first())->id
                : null;

            $in4->cote(
                array_values(array_filter([$dsi?->id, $drh?->id, $dag?->id])),
                $instrSuite?->id,
                'Chaque direction traite le volet qui la concerne et rend compte.',
                $dgUser->id,
                array_filter([
                    $dsi?->id => $activites($dsi, 'APPLICATION'),
                    $drh?->id => $activites($drh, 'GESTION DU PERSONNEL'),
                    $dag?->id => $activites($dag, 'GESTION DU COURRIER'),
                ])
            );

            // La DSI a déjà validé sa réception : 1/3 au tableau de bord.
            $in4->confirmReceptionForOrg($dsi->id, $dirDsi->id);
            $in4->update(['deadline' => now()->subDays(2), 'status' => MailStatusEnum::IN_PROGRESS]);
        }

        // =====================================================================
        // 10. RÉPONSES CHAÎNÉES — un courrier reçu débouche sur plusieurs courriers
        // =====================================================================
        if ($in1 ?? null) {
            if ($in1->replies()->doesntExist()) {
                $in1->createReply($dirDsi, 'RE : ' . $in1->name, "Éléments de réponse transmis au Ministère.", Mail::TYPE_OUTGOING);
                $in1->createReply($dirDsi, 'Note interne — suites à donner', 'Transmission au service concerné pour exécution.', Mail::TYPE_INTERNAL);
            }
        }

        // --- Historique (traçabilité affichée dans la timeline de la fiche) ---
        $this->seedHistory($in2, [
            ['created', $accueil ?? $dgUser, "Courrier déposé à l'accueil et enregistré", now()->subDays(5)],
            ['coted', $dgUser, 'Coté par le DG à la DRH — instruction : Donner suite', now()->subDays(4)],
        ]);

        $this->seedHistory($in3, [
            ['created', $accueil ?? $dgUser, "Courrier déposé à l'accueil et enregistré", now()->subDays(20)],
            ['coted', $dgUser, 'Coté par le DG à la DSI — instruction : Classer', now()->subDays(18)],
            ['reception_confirmed', $dirDsi, 'Réception validée par le directeur DSI', now()->subDays(15)],
        ]);

        $this->seedHistory($out2, [
            ['created', $agentDrh ?? $dirDrh, 'Courrier initié par un agent de la DRH', now()->subDays(3)],
            ['submitted_for_approval', $agentDrh ?? $dirDrh, 'Soumis au visa hiérarchique avec note explicative', now()->subDays(2)],
        ]);

        $this->seedHistory($out3, [
            ['created', $dirDag ?? $dgUser, 'Courrier initié par le directeur DAG (sans visa intermédiaire)', now()->subDays(12)],
            ['submitted_for_approval', $dirDag ?? $dgUser, 'Proposé à la signature du DG', now()->subDays(11)],
            ['dg_signed', $dgUser, 'Signé par le DG — Bon pour envoi', now()->subDays(10)],
        ]);

        $this->seedHistory($out4, [
            ['created', $agentDsi ?? $dirDsi, 'Courrier initié par un agent de la DSI', now()->subDays(8)],
            ['submitted_for_approval', $agentDsi ?? $dirDsi, 'Soumis au visa hiérarchique', now()->subDays(7)],
            ['dg_rejected', $dgUser, 'Rejeté par le DG — chiffrage à préciser', now()->subDays(6)],
        ]);

        // --- Contenants, lots et archivage (données annexes du module) ---
        $container1 = MailContainer::firstOrCreate(
            ['code' => 'MC-2026-001'],
            ['name' => 'Classeur Courriers Entrants 2026', 'property_id' => null, 'created_by' => $dgUser->id, 'creator_organisation_id' => $dag->id]
        );
        MailContainer::firstOrCreate(
            ['code' => 'MC-2026-002'],
            ['name' => 'Classeur Courriers Sortants 2026', 'property_id' => null, 'created_by' => $dgUser->id, 'creator_organisation_id' => $dag->id]
        );

        $batch1 = Batch::firstOrCreate(
            ['code' => 'BT-26-001'],
            ['name' => 'Lot courriers entrants - Semaine en cours', 'organisation_holder_id' => $dag->id]
        );
        $batch2 = Batch::firstOrCreate(
            ['code' => 'BT-26-002'],
            ['name' => 'Lot courriers sortants - Mois en cours', 'organisation_holder_id' => $dag->id]
        );

        foreach ([$in1, $in2, $in3] as $mail) {
            DB::table('batch_mail')->updateOrInsert(
                ['batch_id' => $batch1->id, 'mail_id' => $mail->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        BatchTransaction::firstOrCreate(
            ['batch_id' => $batch1->id, 'organisation_send_id' => $dag->id],
            ['organisation_received_id' => $dg->id]
        );
        BatchTransaction::firstOrCreate(
            ['batch_id' => $batch2->id, 'organisation_send_id' => $dg->id],
            ['organisation_received_id' => $dag->id]
        );

        foreach ([$in3, $int1] as $mail) {
            DB::table('mail_archives')->updateOrInsert(
                ['mail_id' => $mail->id, 'container_id' => $container1->id],
                ['archived_by' => $dgUser->id, 'document_type' => 'original', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info('Module Courrier : ' . count($createdMails) . ' courriers de démonstration couvrant tout le circuit.');
    }

    /**
     * Crée les entrées d'historique d'un courrier (idempotent).
     *
     * @param  array<int, array{0:string,1:?User,2:string,3:\Illuminate\Support\Carbon}>  $events
     */
    private function seedHistory(Mail $mail, array $events): void
    {
        foreach ($events as [$action, $user, $description, $at]) {
            if (!$user) {
                continue;
            }

            $history = MailHistory::firstOrCreate(
                ['mail_id' => $mail->id, 'action' => $action, 'user_id' => $user->id],
                [
                    'description' => $description,
                    'ip_address' => '127.0.0.1',
                ]
            );

            // Aligner l'horodatage sur la chronologie du circuit (pour les délais par étape).
            $history->forceFill(['created_at' => $at, 'updated_at' => $at])->save();
        }
    }
}
