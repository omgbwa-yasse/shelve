<?php

namespace App\Services;

use App\Enums\NotificationModule;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Créer une notification pour un utilisateur
     *
     * @param string $message Le message de la notification
     * @param string $module Le module concerné (utiliser NotificationModule enum)
     * @param int $userId L'ID de l'utilisateur concerné
     * @param string|null $action L'action qui a déclenché la notification
     * @param int|null $resourceId L'ID de la ressource concernée
     * @param string|null $resourceType Le type de ressource concernée
     * @return Notification
     */
    public function createUserNotification(
        string $message,
        string $module,
        int $userId,
        ?string $action = null,
        ?int $resourceId = null,
        ?string $resourceType = null
    ): Notification {
        return Notification::create([
            'message' => $message,
            'module' => $module,
            'user_id' => $userId,
            'action' => $action,
            'resource_id' => $resourceId,
            'resource_type' => $resourceType,
            'read_at' => null
        ]);
    }

    /**
     * Créer une notification pour une organisation
     *
     * @param string $message Le message de la notification
     * @param string $module Le module concerné (utiliser NotificationModule enum)
     * @param int $organisationId L'ID de l'organisation concernée
     * @param string|null $action L'action qui a déclenché la notification
     * @param int|null $resourceId L'ID de la ressource concernée
     * @param string|null $resourceType Le type de ressource concernée
     * @return Notification
     */
    public function createOrganisationNotification(
        string $message,
        string $module,
        int $organisationId,
        ?string $action = null,
        ?int $resourceId = null,
        ?string $resourceType = null
    ): Notification {
        return Notification::create([
            'message' => $message,
            'module' => $module,
            'organisation_id' => $organisationId,
            'action' => $action,
            'resource_id' => $resourceId,
            'resource_type' => $resourceType,
            'read_at' => null
        ]);
    }

    /**
     * Créer une notification pour l'action de création d'une ressource
     *
     * @param string $resourceName Nom de la ressource
     * @param string $module Module concerné
     * @param int|null $userId ID de l'utilisateur (si notification utilisateur)
     * @param int|null $organisationId ID de l'organisation (si notification organisation)
     * @param int|null $resourceId ID de la ressource créée
     * @param string|null $resourceType Type de la ressource
     * @return Notification
     */
    public function notifyResourceCreated(
        string $resourceName,
        string $module,
        ?int $userId = null,
        ?int $organisationId = null,
        ?int $resourceId = null,
        ?string $resourceType = null
    ): Notification {
        $message = "Création de {$resourceName}";
        $action = 'create';

        if ($userId) {
            return $this->createUserNotification($message, $module, $userId, $action, $resourceId, $resourceType);
        } else {
            return $this->createOrganisationNotification($message, $module, $organisationId, $action, $resourceId, $resourceType);
        }
    }

    /**
     * Créer une notification pour l'action de suppression d'une ressource
     *
     * @param string $resourceName Nom de la ressource
     * @param string $module Module concerné
     * @param int|null $userId ID de l'utilisateur (si notification utilisateur)
     * @param int|null $organisationId ID de l'organisation (si notification organisation)
     * @param int|null $resourceId ID de la ressource supprimée
     * @param string|null $resourceType Type de la ressource
     * @return Notification
     */
    public function notifyResourceDeleted(
        string $resourceName,
        string $module,
        ?int $userId = null,
        ?int $organisationId = null,
        ?int $resourceId = null,
        ?string $resourceType = null
    ): Notification {
        $message = "Suppression de {$resourceName}";
        $action = 'delete';

        if ($userId) {
            return $this->createUserNotification($message, $module, $userId, $action, $resourceId, $resourceType);
        } else {
            return $this->createOrganisationNotification($message, $module, $organisationId, $action, $resourceId, $resourceType);
        }
    }
}
