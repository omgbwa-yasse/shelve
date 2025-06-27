<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

abstract class BasePolicy
{
    /**
     * Perform pre-authorization checks.
     * This method runs before any other policy method.
     */
    public function before(User $user, string $ability): bool|null
    {
        // Grant all abilities to super administrators
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Check if user has a current organisation (required for most actions)
        if (!$user->currentOrganisation && !in_array($ability, $this->getGuestAllowedAbilities())) {
            return false;
        }

        return null; // Continue to the specific policy method
    }

    /**
     * Get abilities that don't require a current organisation.
     * Override in child classes if needed.
     */
    protected function getGuestAllowedAbilities(): array
    {
        return [];
    }

    /**
     * Check if the user has the required permission for their current organisation.
     */
    protected function hasPermission(User $user, string $permission): bool
    {
        return $user->currentOrganisation &&
               $user->hasPermissionTo($permission, $user->currentOrganisation);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     * This method handles various ways models can be linked to organisations.
     */
    protected function checkOrganisationAccess(User $user, Model $model): bool
    {
        if (!$user->currentOrganisation) {
            return false;
        }

        $modelClass = get_class($model);
        $modelName = class_basename($modelClass);
        $cacheKey = strtolower($modelName) . "_org_access:{$user->id}:{$model->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $model) {
            return $this->performOrganisationCheck($user, $model);
        });
    }

    /**
     * Perform the actual organisation access check.
     * This method contains the logic for checking organisation access.
     */
    private function performOrganisationCheck(User $user, Model $model): bool
    {
        // 1. For models directly linked to organisations (many-to-many)
        if ($this->checkDirectOrganisationLink($model, $user->current_organisation_id)) {
            return true;
        }

        // 2. For models with organisation_id column (belongs to organisation)
        if ($this->checkOrganisationIdColumn($model, $user->current_organisation_id)) {
            return true;
        }

        // 3. For models linked through activity (like Records)
        if ($this->checkActivityOrganisationLink($model, $user->current_organisation_id)) {
            return true;
        }

        // 4. For models linked through user (belongs to user)
        if ($this->checkUserOrganisationLink($model, $user->current_organisation_id)) {
            return true;
        }

        // 5. For models linked through building/location hierarchy
        if ($this->checkBuildingOrganisationLink($user, $model)) {
            return true;
        }

        // Default: deny access if no specific organisation link found
        return false;
    }

    /**
     * Check direct organisation link (many-to-many relationship).
     */
    private function checkDirectOrganisationLink(Model $model, int $organisationId): bool
    {
        if (!method_exists($model, 'organisations')) {
            return false;
        }

        foreach($model->organisations as $organisation) {
            if ($organisation->id == $organisationId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check organisation_id column.
     */
    private function checkOrganisationIdColumn(Model $model, int $organisationId): bool
    {
        return isset($model->organisation_id) && $model->organisation_id == $organisationId;
    }

    /**
     * Check organisation link through activity.
     */
    private function checkActivityOrganisationLink(Model $model, int $organisationId): bool
    {
        if (!method_exists($model, 'activity') || !$model->activity) {
            return false;
        }

        foreach($model->activity->organisations as $organisation) {
            if ($organisation->id == $organisationId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check organisation link through user.
     */
    private function checkUserOrganisationLink(Model $model, int $organisationId): bool
    {
        if (!isset($model->user_id)) {
            return false;
        }

        $modelUser = $model->user;
        if (!$modelUser || !$modelUser->organisations) {
            return false;
        }

        foreach($modelUser->organisations as $organisation) {
            if ($organisation->id == $organisationId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check organisation link through building hierarchy.
     */
    private function checkBuildingOrganisationLink(User $user, Model $model): bool
    {
        if (!method_exists($model, 'building') || !$model->building) {
            return false;
        }

        return $this->checkOrganisationAccess($user, $model->building);
    }

    /**
     * Create a detailed authorization response with a custom message.
     */
    protected function deny(string $message = 'Cette action n\'est pas autorisée.'): Response
    {
        return Response::deny($message);
    }

    /**
     * Create a 404 response to hide the existence of a resource.
     */
    protected function denyAsNotFound(): Response
    {
        return Response::denyAsNotFound();
    }

    /**
     * Allow the action.
     */
    protected function allow(): Response
    {
        return Response::allow();
    }

    /**
     * Check if user can perform viewAny action.
     */
    protected function canViewAny(User $user, string $permission): bool|Response
    {
        if (!$this->hasPermission($user, $permission)) {
            return $this->deny('Vous n\'avez pas la permission de voir ces éléments.');
        }
        return true;
    }

    /**
     * Check if user can view a specific model.
     */
    protected function canView(User $user, Model $model, string $permission): bool|Response
    {
        if (!$this->hasPermission($user, $permission)) {
            return $this->denyAsNotFound();
        }

        if (!$this->checkOrganisationAccess($user, $model)) {
            return $this->denyAsNotFound();
        }

        return true;
    }

    /**
     * Check if user can create models.
     */
    protected function canCreate(User $user, string $permission): bool|Response
    {
        if (!$this->hasPermission($user, $permission)) {
            return $this->deny('Vous n\'avez pas la permission de créer cet élément.');
        }
        return true;
    }

    /**
     * Check if user can update a specific model.
     */
    protected function canUpdate(User $user, Model $model, string $permission): bool|Response
    {
        if (!$this->hasPermission($user, $permission)) {
            return $this->deny('Vous n\'avez pas la permission de modifier cet élément.');
        }

        if (!$this->checkOrganisationAccess($user, $model)) {
            return $this->denyAsNotFound();
        }

        return true;
    }

    /**
     * Check if user can delete a specific model.
     */
    protected function canDelete(User $user, Model $model, string $permission): bool|Response
    {
        if (!$this->hasPermission($user, $permission)) {
            return $this->deny('Vous n\'avez pas la permission de supprimer cet élément.');
        }

        if (!$this->checkOrganisationAccess($user, $model)) {
            return $this->denyAsNotFound();
        }

        return true;
    }

    /**
     * Check if user can force delete a specific model.
     */
    protected function canForceDelete(User $user, Model $model, string $permission): bool|Response
    {
        if (!$this->hasPermission($user, $permission)) {
            return $this->deny('Vous n\'avez pas la permission de supprimer définitivement cet élément.');
        }

        if (!$this->checkOrganisationAccess($user, $model)) {
            return $this->denyAsNotFound();
        }

        return true;
    }
}
