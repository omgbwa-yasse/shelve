<?php

namespace App\Services;

use App\Models\Notification;
use App\Enums\NotificationModule;
use App\Enums\NotificationAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Créer une notification pour un utilisateur spécifique
     */
    public function createForUser(
        int $userId,
        NotificationModule $module,
        string $name,
        NotificationAction $action,
        ?string $message = null,
        ?Model $relatedEntity = null,
        ?int $organisationId = null
    ): Notification {
        try {
            return Notification::create([
                'user_id' => $userId,
                'organisation_id' => $organisationId ?? $this->getCurrentOrganisationId(),
                'module' => $module,
                'name' => $name,
                'message' => $message,
                'action' => $action,
                'related_entity_type' => $relatedEntity ? class_basename($relatedEntity) : null,
                'related_entity_id' => $relatedEntity?->id,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur création notification utilisateur: ' . $e->getMessage(), [
                'user_id' => $userId,
                'module' => $module->value,
                'action' => $action->value,
                'name' => $name
            ]);
            throw $e;
        }
    }

    /**
     * Créer une notification pour toute une organisation
     */
    public function createForOrganisation(
        int $organisationId,
        NotificationModule $module,
        string $name,
        NotificationAction $action,
        ?string $message = null,
        ?Model $relatedEntity = null
    ): Notification {
        try {
            return Notification::create([
                'organisation_id' => $organisationId,
                'module' => $module,
                'name' => $name,
                'message' => $message,
                'action' => $action,
                'related_entity_type' => $relatedEntity ? class_basename($relatedEntity) : null,
                'related_entity_id' => $relatedEntity?->id,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur création notification organisation: ' . $e->getMessage(), [
                'organisation_id' => $organisationId,
                'module' => $module->value,
                'action' => $action->value,
                'name' => $name
            ]);
            throw $e;
        }
    }

    /**
     * Créer automatiquement une notification basée sur l'événement CRUD d'un modèle
     */
    public function notifyModelEvent(
        Model $model,
        NotificationAction $action,
        ?string $customMessage = null,
        ?int $specificUserId = null,
        ?int $specificOrganisationId = null
    ): ?Notification {
        try {
            $module = $this->getModuleFromModel($model);
            if (!$module) {
                return null;
            }

            $name = $this->getModelDisplayName($model);
            $message = $customMessage ?? $this->generateDefaultMessage($model, $action);

            // Si un utilisateur spécifique est défini, créer pour lui
            if ($specificUserId) {
                return $this->createForUser(
                    $specificUserId,
                    $module,
                    $name,
                    $action,
                    $message,
                    $model,
                    $specificOrganisationId
                );
            }

            // Sinon, créer pour l'organisation courante
            $organisationId = $specificOrganisationId ?? $this->getCurrentOrganisationId();
            if ($organisationId) {
                return $this->createForOrganisation(
                    $organisationId,
                    $module,
                    $name,
                    $action,
                    $message,
                    $model
                );
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erreur notification événement modèle: ' . $e->getMessage(), [
                'model' => get_class($model),
                'action' => $action->value,
                'model_id' => $model->id ?? 'N/A'
            ]);
            return null;
        }
    }

    /**
     * Marquer plusieurs notifications comme lues
     */
    public function markAsRead(array $notificationIds): int
    {
        return Notification::whereIn('id', $notificationIds)
            ->update(['is_read' => true]);
    }

    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsReadForUser(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Marquer toutes les notifications d'une organisation comme lues
     */
    public function markAllAsReadForOrganisation(int $organisationId): int
    {
        return Notification::where('organisation_id', $organisationId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Obtenir les notifications non lues pour un utilisateur
     */
    public function getUnreadForUser(int $userId, int $limit = 50)
    {
        return Notification::forUser($userId)
            ->unread()
            ->with(['user', 'organisation'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les notifications non lues pour une organisation
     */
    public function getUnreadForOrganisation(int $organisationId, int $limit = 50)
    {
        return Notification::forOrganisation($organisationId)
            ->unread()
            ->with(['user', 'organisation'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Compter les notifications non lues pour un utilisateur
     */
    public function countUnreadForUser(int $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * Compter les notifications non lues pour une organisation
     */
    public function countUnreadForOrganisation(int $organisationId): int
    {
        return Notification::forOrganisation($organisationId)->unread()->count();
    }

    /**
     * Nettoyer les anciennes notifications (plus de X jours)
     */
    public function cleanupOld(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Obtenir le module correspondant au modèle
     */
    private function getModuleFromModel(Model $model): ?NotificationModule
    {
        $modelClass = class_basename($model);

        return match($modelClass) {
            'BulletinBoard', 'Event', 'Post' => NotificationModule::BULLETIN_BOARDS,
            'Mail', 'MailContainer', 'MailTypology' => NotificationModule::MAILS,
            'Record', 'Container', 'Support' => NotificationModule::RECORDS,
            'Communication' => NotificationModule::COMMUNICATIONS,
            'Transfer', 'Transferring' => NotificationModule::TRANSFERS,
            'Deposit' => NotificationModule::DEPOSITS,
            'Tool' => NotificationModule::TOOLS,
            'Dolly' => NotificationModule::DOLLIES,
            'Workflow', 'WorkflowStep', 'Task' => NotificationModule::WORKFLOWS,
            'User', 'Organisation', 'Author' => NotificationModule::CONTACTS,
            'AiModel', 'AiAction' => NotificationModule::AI,
            'PublicNews', 'PublicPage', 'PublicEvent' => NotificationModule::PUBLIC,
            'Setting', 'SystemSetting' => NotificationModule::SETTINGS,
            default => null,
        };
    }

    /**
     * Obtenir un nom d'affichage pour le modèle
     */
    private function getModelDisplayName(Model $model): string
    {
        // Essayer d'utiliser des propriétés communes pour le nom
        if (isset($model->title)) {
            return $model->title;
        }
        if (isset($model->name)) {
            return $model->name;
        }
        if (isset($model->code)) {
            return $model->code;
        }
        if (isset($model->reference)) {
            return $model->reference;
        }

        // Fallback sur la classe et l'ID
        return class_basename($model) . ' #' . ($model->id ?? 'N/A');
    }

    /**
     * Générer un message par défaut
     */
    private function generateDefaultMessage(Model $model, NotificationAction $action): string
    {
        $modelName = $this->getModelDisplayName($model);
        $actionLabel = $action->label();
        $moduleLabel = $this->getModuleFromModel($model)?->label() ?? 'Système';

        return "L'élément '{$modelName}' a été {$actionLabel} dans le module {$moduleLabel}";
    }

    /**
     * Obtenir l'ID de l'organisation courante
     */
    private function getCurrentOrganisationId(): ?int
    {
        $user = Auth::user();
        return $user?->organisation_id ?? $user?->current_organisation_id ?? null;
    }
}
