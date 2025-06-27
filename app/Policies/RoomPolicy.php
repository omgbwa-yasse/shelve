<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class RoomPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('room_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Room $room): bool
    {
        return $user->hasPermissionTo('room_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $room);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('room_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Room $room): bool
    {
        return $user->hasPermissionTo('room_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $room);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Room $room): bool
    {
        return $user->hasPermissionTo('room_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $room);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Room $room): bool
    {
        return $user->hasPermissionTo('room_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $room);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Room $room): bool
    {
        $cacheKey = "room_org_access:{$user->id}:{$room->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $room) {
            // For models directly linked to organisations
            if (method_exists($room, 'organisations')) {
                foreach($room->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($room->organisation_id)) {
                return $room->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($room, 'activity') && $room->activity) {
                foreach($room->activity->organisations as $organisation) {
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
