<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mail;
use App\Models\User;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Enums\MailStatusEnum;
use App\Services\MailNotificationService;
use Carbon\Carbon;

class MailTrackingTestSeeder extends Seeder
{
    public function run(): void
    {
        $notificationService = app(MailNotificationService::class);

        // Créer quelques utilisateurs de test si ils n'existent pas
        $users = User::all();
        if ($users->count() < 1) {
            $this->command->info('Aucun utilisateur trouvé. Créez au moins 1 utilisateur.');
            return;
        }

        // Utiliser les utilisateurs existants (répéter si nécessaire)
        $user1 = $users->first();
        $user2 = $users->count() > 1 ? $users->skip(1)->first() : $user1;
        $user3 = $users->count() > 2 ? $users->skip(2)->first() : $user1;

        $organisation = Organisation::first();
        $priority = MailPriority::first();
        $typology = MailTypology::first();

        if (!$organisation || !$priority || !$typology) {
            $this->command->info('Données de base manquantes (organisation, priorité, typologie)');
            return;
        }

        // 1. Courrier avec échéance approchante (dans 2 heures)
        $mailApproaching = Mail::create([
            'code' => 'TEST-DEADLINE-001',
            'name' => 'Courrier test échéance approchante',
            'date' => now(),
            'description' => 'Test pour vérifier les notifications d\'échéance approchante',
            'status' => MailStatusEnum::IN_PROGRESS,
            'priority_id' => $priority->id,
            'typology_id' => $typology->id,
            'recipient_organisation_id' => $organisation->id,
            'mail_type' => 'incoming',
            'deadline' => now()->addHours(2),
            'assigned_to' => $user1->id,
            'assigned_at' => now()->subHours(1),
        ]);

        // 2. Courrier en retard (échéance dépassée depuis 1 jour)
        $mailOverdue = Mail::create([
            'code' => 'TEST-OVERDUE-001',
            'name' => 'Courrier test en retard',
            'date' => now()->subDays(3),
            'description' => 'Test pour vérifier les notifications de retard',
            'status' => MailStatusEnum::IN_PROGRESS,
            'priority_id' => $priority->id,
            'typology_id' => $typology->id,
            'recipient_organisation_id' => $organisation->id,
            'mail_type' => 'incoming',
            'deadline' => now()->subDay(),
            'assigned_to' => $user2->id,
            'assigned_at' => now()->subDays(2),
        ]);

        // 3. Courrier nécessitant une approbation
        $mailApproval = Mail::create([
            'code' => 'TEST-APPROVAL-001',
            'name' => 'Courrier test approbation',
            'date' => now(),
            'description' => 'Test pour vérifier le workflow d\'approbation',
            'status' => MailStatusEnum::PENDING_APPROVAL,
            'priority_id' => $priority->id,
            'typology_id' => $typology->id,
            'recipient_organisation_id' => $organisation->id,
            'mail_type' => 'incoming',
            'deadline' => now()->addDays(3),
            'assigned_to' => $user3->id,
            'assigned_at' => now()->subHours(2),
        ]);

        // 4. Courrier normal pour tests généraux
        $mailNormal = Mail::create([
            'code' => 'TEST-NORMAL-001',
            'name' => 'Courrier test normal',
            'date' => now(),
            'description' => 'Test pour les fonctionnalités générales',
            'status' => MailStatusEnum::DRAFT,
            'priority_id' => $priority->id,
            'typology_id' => $typology->id,
            'recipient_organisation_id' => $organisation->id,
            'mail_type' => 'incoming',
            'deadline' => now()->addWeek(),
        ]);

        // Initialiser les workflows
        foreach ([$mailApproaching, $mailOverdue, $mailApproval, $mailNormal] as $mail) {
            $mail->initializeWorkflow();
        }        // Créer quelques notifications de test
        $notificationService->notifyAssignment($mailApproaching, $user1, 'Assignation automatique pour test');
        $notificationService->notifyAssignment($mailOverdue, $user2, 'Assignation automatique pour test');

        // Notification d'approbation
        $notificationService->notifyApprovalRequired($mailApproval, $user3);

        // Simuler quelques changements d'état pour créer l'historique
        $mailNormal->updateStatus(MailStatusEnum::PENDING_REVIEW, 'Passage en révision pour test');
        $mailNormal->updateStatus(MailStatusEnum::IN_PROGRESS, 'Début du traitement pour test');

        $this->command->info('✅ Données de test créées avec succès :');
        $this->command->info("   - Courrier échéance approchante: {$mailApproaching->code}");
        $this->command->info("   - Courrier en retard: {$mailOverdue->code}");
        $this->command->info("   - Courrier nécessitant approbation: {$mailApproval->code}");
        $this->command->info("   - Courrier normal: {$mailNormal->code}");
        $this->command->info('');
        $this->command->info('📧 Notifications créées pour les utilisateurs assignés');
        $this->command->info('🔄 Workflows initialisés');
        $this->command->info('📊 Historiques créés');
        $this->command->info('');
        $this->command->info('🧪 Pour tester :');
        $this->command->info('   - Visitez /mails/notifications/show pour voir les notifications');
        $this->command->info('   - Exécutez php artisan mail:process-notifications pour générer plus de notifications');
        $this->command->info('   - Vérifiez les historiques dans la base de données');
    }
}
