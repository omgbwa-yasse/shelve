<?php

namespace App\Policies;

use App\Models\PublicUser;
use App\Models\PublicEvent;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicEventPolicy
{
    use HandlesAuthorization;    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(): bool
    {
        return true; // Les événements sont publics
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(): bool
    {
        return true; // Les événements sont publics
    }

    /**
     * Determine whether the user can register for the event.
     */
    public function register(PublicUser $user, PublicEvent $event): bool
    {
        return $user->is_approved
            && $user->email_verified_at !== null
            && $event->start_date > now();
    }

    /**
     * Determine whether the user can cancel their registration.
     */
    public function cancelRegistration(PublicUser $user, PublicEvent $event): bool
    {
        return $user->is_approved
            && $event->start_date > now()->addHours(24); // 24h avant l'événement
    }
}
