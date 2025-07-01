<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MailNotificationService;
use App\Models\Mail;
use App\Models\MailWorkflow;

class ProcessMailNotifications extends Command
{
    protected $signature = 'mail:process-notifications
                           {--type=all : Type de notifications à traiter (deadlines, overdue, actions, all)}
                           {--hours=24 : Nombre d\'heures avant échéance pour les alertes}';

    protected $description = 'Traiter les notifications automatiques pour les courriers';

    public function __construct(private MailNotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = $this->option('type');
        $hours = intval($this->option('hours'));

        $this->info("Démarrage du traitement des notifications de type: {$type}");

        try {
            $totalNotifications = 0;

            if ($type === 'all' || $type === 'deadlines') {
                $this->info('Traitement des échéances approchantes...');
                $notifications = $this->notificationService->notifyApproachingDeadlines($hours);
                $count = $notifications->count();
                $totalNotifications += $count;
                $this->info("✓ {$count} notifications d'échéances créées");
            }

            if ($type === 'all' || $type === 'overdue') {
                $this->info('Traitement des courriers en retard...');
                $notifications = $this->notificationService->notifyOverdueMails();
                $count = $notifications->count();
                $totalNotifications += $count;
                $this->info("✓ {$count} notifications de retard créées");
            }

            if ($type === 'all' || $type === 'actions') {
                $this->info('Traitement des actions à effectuer...');
                $notifications = $this->notificationService->notifyActionDeadlines();
                $count = $notifications->count();
                $totalNotifications += $count;
                $this->info("✓ {$count} notifications d'actions créées");
            }

            if ($type === 'all' || $type === 'escalation') {
                $this->info('Traitement des escalades automatiques...');
                $escalated = $this->processAutoEscalation();
                $this->info("✓ {$escalated} courriers escaladés");
            }

            $this->info("Total: {$totalNotifications} notifications créées");
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Erreur lors du traitement: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function processAutoEscalation(): int
    {
        $workflows = MailWorkflow::query()->needsEscalation()->with('mail')->get();
        $escalatedCount = 0;

        foreach ($workflows as $workflow) {
            // Logique d'escalade automatique
            $supervisor = $this->findSupervisor($workflow->current_assignee_id);

            if ($supervisor) {
                $workflow->escalate($supervisor->id, 'Escalade automatique - délai dépassé');
                $escalatedCount++;

                $this->notificationService->createNotification(
                    $workflow->mail,
                    $supervisor,
                    \App\Enums\NotificationTypeEnum::MAIL_ASSIGNED,
                    "Courrier {$workflow->mail->code} escaladé automatiquement"
                );
            }
        }

        return $escalatedCount;
    }

    private function findSupervisor($userId)
    {
        // Logique pour trouver le superviseur
        // À adapter selon votre structure organisationnelle
        return \App\Models\User::where('id', '!=', $userId)
                              ->whereHas('roles', function($q) {
                                  $q->where('name', 'supervisor');
                              })
                              ->first();
    }
}
