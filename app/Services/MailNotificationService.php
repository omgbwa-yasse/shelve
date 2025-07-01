<?php

namespace App\Services;

use App\Models\Mail;
use App\Models\MailNotification;
use App\Models\User;
use App\Enums\NotificationTypeEnum;
use App\Enums\MailStatusEnum;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MailNotificationService
{
    /**
     * Créer une notification pour un courrier
     */
    public function createNotification(
        Mail $mail,
        User $user,
        NotificationTypeEnum $type,
        ?string $customMessage = null,
        ?array $additionalData = []
    ): MailNotification {
        $message = $customMessage ?? $this->getDefaultMessage($mail, $type);

        return MailNotification::create([
            'mail_id' => $mail->id,
            'user_id' => $user->id,
            'type' => $type,
            'title' => $type->title(),
            'message' => $message,
            'priority' => $type->priority(),
            'data' => array_merge([
                'mail_code' => $mail->code,
                'mail_name' => $mail->name,
                'deadline' => $mail->deadline?->format('Y-m-d H:i:s'),
            ], $additionalData)
        ]);
    }

    /**
     * Notifier l'assignation d'un courrier
     */
    public function notifyAssignment(Mail $mail, User $assignee, ?string $reason = null): void
    {
        $this->createNotification(
            $mail,
            $assignee,
            NotificationTypeEnum::MAIL_ASSIGNED,
            $reason ? "Courrier {$mail->code} assigné : {$reason}" : null,
            ['reason' => $reason]
        );
    }

    /**
     * Notifier un changement de statut
     */
    public function notifyStatusChange(
        Mail $mail,
        MailStatusEnum $oldStatus,
        MailStatusEnum $newStatus,
        ?string $reason = null
    ): void {
        if ($mail->assigned_to) {
            $this->createNotification(
                $mail,
                User::find($mail->assigned_to),
                NotificationTypeEnum::MAIL_STATUS_CHANGED,
                "Statut du courrier {$mail->code} modifié de {$oldStatus->label()} à {$newStatus->label()}",
                [
                    'old_status' => $oldStatus->value,
                    'new_status' => $newStatus->value,
                    'reason' => $reason
                ]
            );
        }
    }

    /**
     * Notifier les échéances approchantes
     */
    public function notifyApproachingDeadlines(?int $hours = 24): Collection
    {
        $mails = Mail::approachingDeadline($hours)
                    ->with('assignedUser')
                    ->whereNotNull('assigned_to')
                    ->get();

        $notifications = collect();

        foreach ($mails as $mail) {
            // Vérifier si une notification n'a pas déjà été envoyée récemment
            $existingNotification = MailNotification::where('mail_id', $mail->id)
                ->where('user_id', $mail->assigned_to)
                ->where('type', NotificationTypeEnum::MAIL_DEADLINE_APPROACHING)
                ->where('created_at', '>', now()->subHours(6)) // Pas plus d'une notification toutes les 6h
                ->first();

            if (!$existingNotification) {
                $notification = $this->createNotification(
                    $mail,
                    $mail->assignedUser()->first(),
                    NotificationTypeEnum::MAIL_DEADLINE_APPROACHING,
                    "Le courrier {$mail->code} doit être traité avant {$mail->deadline->format('d/m/Y H:i')}",
                    ['hours_remaining' => now()->diffInHours($mail->deadline)]
                );
                $notifications->push($notification);
            }
        }

        return $notifications;
    }

    /**
     * Notifier les courriers en retard
     */
    public function notifyOverdueMails(): Collection
    {
        $mails = Mail::overdue()
                    ->with('assignedUser')
                    ->whereNotNull('assigned_to')
                    ->get();

        $notifications = collect();

        foreach ($mails as $mail) {
            // Mettre à jour le statut si nécessaire
            if ($mail->status !== MailStatusEnum::OVERDUE) {
                $mail->updateStatus(MailStatusEnum::OVERDUE, 'Échéance dépassée');
            }

            // Créer notification de retard
            $hoursOverdue = now()->diffInHours($mail->deadline);
            $notification = $this->createNotification(
                $mail,
                $mail->assignedUser()->first(),
                NotificationTypeEnum::MAIL_OVERDUE,
                "Le courrier {$mail->code} est en retard de {$hoursOverdue}h",
                ['hours_overdue' => $hoursOverdue]
            );
            $notifications->push($notification);
        }

        return $notifications;
    }

    /**
     * Notifier les actions à effectuer
     */
    public function notifyActionDeadlines(): Collection
    {
        $mails = Mail::whereHas('action')
                    ->with(['action', 'assignedUser'])
                    ->whereNotNull('assigned_to')
                    ->where('status', MailStatusEnum::IN_PROGRESS)
                    ->get();

        $notifications = collect();

        foreach ($mails as $mail) {
            if ($mail->action && $mail->action->duration) {
                $actionDeadline = $mail->assigned_at ? Carbon::parse($mail->assigned_at)->addHours($mail->action->duration) : null;

                if ($actionDeadline && now()->addHours(24)->isAfter($actionDeadline)) {
                    $notification = $this->createNotification(
                        $mail,
                        $mail->assignedUser()->first(),
                        NotificationTypeEnum::ACTION_DEADLINE_APPROACHING,
                        "L'action '{$mail->action->name}' doit être effectuée avant {$actionDeadline->format('d/m/Y H:i')}",
                        ['action_deadline' => $actionDeadline->format('Y-m-d H:i:s')]
                    );
                    $notifications->push($notification);
                }
            }
        }

        return $notifications;
    }

    /**
     * Notifier les demandes d'approbation
     */
    public function notifyApprovalRequired(Mail $mail, User $approver): void
    {
        $this->createNotification(
            $mail,
            $approver,
            NotificationTypeEnum::MAIL_REQUIRES_APPROVAL,
            "Le courrier {$mail->code} nécessite votre approbation"
        );
    }

    /**
     * Obtenir les notifications non lues pour un utilisateur
     */
    public function getUnreadNotifications(User $user, ?int $limit = null)
    {
        $query = MailNotification::unread()
                    ->forUser($user->id)
                    ->with('mail')
                    ->byPriority()
                    ->orderBy('created_at', 'desc');

        return $limit ? $query->limit($limit)->get() : $query->get();
    }

    /**
     * Marquer les notifications comme lues
     */
    public function markAsRead($notificationIds): int
    {
        if (is_array($notificationIds)) {
            return MailNotification::whereIn('id', $notificationIds)
                                  ->update(['read_at' => now()]);
        }

        return MailNotification::where('id', $notificationIds)
                              ->update(['read_at' => now()]);
    }

    /**
     * Obtenir le message par défaut pour un type de notification
     */
    private function getDefaultMessage(Mail $mail, NotificationTypeEnum $type): string
    {
        return match($type) {
            NotificationTypeEnum::MAIL_ASSIGNED => "Le courrier {$mail->code} vous a été assigné",
            NotificationTypeEnum::MAIL_DEADLINE_APPROACHING => "Le courrier {$mail->code} approche de son échéance",
            NotificationTypeEnum::MAIL_OVERDUE => "Le courrier {$mail->code} est en retard",
            NotificationTypeEnum::MAIL_STATUS_CHANGED => "Le statut du courrier {$mail->code} a été modifié",
            NotificationTypeEnum::MAIL_REQUIRES_APPROVAL => "Le courrier {$mail->code} nécessite votre approbation",
            NotificationTypeEnum::MAIL_APPROVED => "Le courrier {$mail->code} a été approuvé",
            NotificationTypeEnum::MAIL_REJECTED => "Le courrier {$mail->code} a été rejeté",
            NotificationTypeEnum::MAIL_TRANSMITTED => "Le courrier {$mail->code} a été transmis",
            NotificationTypeEnum::MAIL_COMPLETED => "Le courrier {$mail->code} a été terminé",
            default => "Notification concernant le courrier {$mail->code}",
        };
    }

    /**
     * Nettoyer les anciennes notifications
     */
    public function cleanupOldNotifications(int $daysOld = 30): int
    {
        return MailNotification::where('created_at', '<', now()->subDays($daysOld))->delete();
    }
}
