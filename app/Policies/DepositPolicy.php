<?php

namespace App\Policies;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class DepositPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('deposit_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Deposit $deposit): bool
    {
        return $user->hasPermissionTo('deposit_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $deposit);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('deposit_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Deposit $deposit): bool
    {
        return $user->hasPermissionTo('deposit_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $deposit);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Deposit $deposit): bool
    {
        return $user->hasPermissionTo('deposit_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $deposit);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Deposit $deposit): bool
    {
        return $user->hasPermissionTo('deposit_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $deposit);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Deposit $deposit): bool
    {
        $cacheKey = "deposit_org_access:{$user->id}:{$deposit->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $deposit) {
            // For models directly linked to organisations
            if (method_exists($deposit, 'organisations')) {
                foreach($deposit->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($deposit->organisation_id)) {
                return $deposit->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($deposit, 'activity') && $deposit->activity) {
                foreach($deposit->activity->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // Default: allow access if no specific organisation restriction
            return true;
        });
    }
}
