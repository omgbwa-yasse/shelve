<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class ReservationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('reservation_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('reservation_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('reservation_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservation $reservation): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('reservation_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('reservation_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reservation $reservation): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('reservation_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Reservation $reservation): bool
    {
        $cacheKey = "reservation_org_access:{$user->id}:{$reservation->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $reservation) {
            // For models directly linked to organisations
            if (method_exists($reservation, 'organisations')) {
                foreach($reservation->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($reservation->organisation_id)) {
                return $reservation->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($reservation, 'activity') && $reservation->activity) {
                foreach($reservation->activity->organisations as $organisation) {
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
