<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

abstract class BasePolicy
{
    /**
     * Perform pre-authorization checks.
     * This method runs before any other policy method.
     */
    public function before(User $user, string $ability): bool|null
    {
        // Grant all abilities to super administrators
        if ($user->isSuperAdmin()) {
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
     * Check if the user has the required permission using Gate.
     */
    protected function hasPermission(User $user, string $permission): bool
    {
        return Gate::forUser($user)->allows($permission);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     * This method handles various ways models can be linked to organisations.
     * Now integrates with Gate for organization access checks.
     */
    protected function checkOrganisationAccess(User $user, Model $model): bool
    {
        // Use Gate to check organisation access
        return Gate::forUser($user)->allows('access-in-organisation', $model);
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
