<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class EventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('event_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('event_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('event_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('event_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('event_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('event_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Event $event): bool
    {
        $cacheKey = "event_org_access:{$user->id}:{$event->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $event) {
            // For models directly linked to organisations
            if (method_exists($event, 'organisations')) {
                foreach($event->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($event->organisation_id)) {
                return $event->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($event, 'activity') && $event->activity) {
                foreach($event->activity->organisations as $organisation) {
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
