<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class EventPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'event_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event): bool|Response
    {
        return $this->canView($user, $event, 'event_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'event_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool|Response
    {
        return $this->canUpdate($user, $event, 'event_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool|Response
    {
        return $this->canDelete($user, $event, 'event_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool|Response
    {
        return $this->canForceDelete($user, $event, 'event_force_delete');
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
