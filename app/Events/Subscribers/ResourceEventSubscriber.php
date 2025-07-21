<?php

namespace App\Events\Subscribers;

use App\Enums\NotificationModule;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ResourceEventSubscriber
{
    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Créer un nouvel écouteur d'événements
     *
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Gérer les événements de création de modèles
     *
     * @param mixed $unused
     * @param array $models
     * @return void
     */
    public function handleCreated($unused, array $models)
    {
        foreach ($models as $model) {
            $this->processCreatedEvent($model);
        }
    }

    /**
     * Gérer les événements de suppression de modèles
     *
     * @param mixed $unused
     * @param array $models
     * @return void
     */
    public function handleDeleted($unused, array $models)
    {
        foreach ($models as $model) {
            $this->processDeletedEvent($model);
        }
    }

    /**
     * Traiter l'événement de création
     *
     * @param Model $model
     * @return void
     */
    protected function processCreatedEvent(Model $model)
    {
        $resourceType = $this->getModelName($model);
        $resourceName = $this->getResourceDisplayName($model);
        $module = $this->determineModule($model);

        // Déterminer si la notification est pour un utilisateur ou une organisation
        $userId = Auth::id();
        $organisationId = method_exists(Auth::class, 'currentOrganisationId') ?
            Auth::currentOrganisationId() :
            null;

        $modelId = $model->getKey();

        if ($userId) {
            $this->notificationService->notifyResourceCreated(
                $resourceName,
                $module,
                $userId,
                null,
                $modelId,
                $resourceType
            );
        }

        if ($organisationId) {
            $this->notificationService->notifyResourceCreated(
                $resourceName,
                $module,
                null,
                $organisationId,
                $modelId,
                $resourceType
            );
        }
    }

    /**
     * Traiter l'événement de suppression
     *
     * @param Model $model
     * @return void
     */
    protected function processDeletedEvent(Model $model)
    {
        $resourceType = $this->getModelName($model);
        $resourceName = $this->getResourceDisplayName($model);
        $module = $this->determineModule($model);

        // Déterminer si la notification est pour un utilisateur ou une organisation
        $userId = Auth::id();
        $organisationId = method_exists(Auth::class, 'currentOrganisationId') ?
            Auth::currentOrganisationId() :
            null;

        $modelId = $model->getKey();

        if ($userId) {
            $this->notificationService->notifyResourceDeleted(
                $resourceName,
                $module,
                $userId,
                null,
                $modelId,
                $resourceType
            );
        }

        if ($organisationId) {
            $this->notificationService->notifyResourceDeleted(
                $resourceName,
                $module,
                null,
                $organisationId,
                $modelId,
                $resourceType
            );
        }
    }

    /**
     * Obtenir le nom du modèle
     *
     * @param Model $model
     * @return string
     */
    protected function getModelName(Model $model): string
    {
        return class_basename($model);
    }

    /**
     * Obtenir le nom à afficher pour la ressource
     *
     * @param Model $model
     * @return string
     */
    protected function getResourceDisplayName(Model $model): string
    {
        $name = $this->getModelName($model);

        // Vérifier les attributs courants pour un nom descriptif
        if (method_exists($model, 'getDisplayName')) {
            $name = $model->getDisplayName();
        } elseif ($model->getAttribute('name')) {
            $name = $model->getAttribute('name');
        } elseif ($model->getAttribute('title')) {
            $name = $model->getAttribute('title');
        } elseif ($model->getAttribute('label')) {
            $name = $model->getAttribute('label');
        }

        return $name;
    }

    /**
     * Déterminer le module associé au modèle
     *
     * @param Model $model
     * @return string
     */
    protected function determineModule(Model $model): string
    {
        $className = get_class($model);

        // Déterminer le module basé sur l'espace de noms ou le nom de la classe
        if (Str::contains($className, 'BulletinBoard')) {
            return NotificationModule::BULLETIN_BOARDS->value;
        }

        // Ajouter d'autres mappings de modules ici
        // Par exemple:
        // if (Str::contains($className, 'OtherModule')) {
        //     return NotificationModule::OTHER_MODULE->value;
        // }

        // Valeur par défaut
        return NotificationModule::BULLETIN_BOARDS->value;
    }

    /**
     * Enregistrer les écouteurs pour l'abonné.
     *
     * @param \Illuminate\Events\Dispatcher $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            'eloquent.created: *',
            [ResourceEventSubscriber::class, 'handleCreated']
        );

        $events->listen(
            'eloquent.deleted: *',
            [ResourceEventSubscriber::class, 'handleDeleted']
        );
    }
}
