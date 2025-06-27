<?php

namespace App\Policies;

use App\Models\PublicUser;
use App\Models\PublicEvent;
use Illuminate\Auth\Access\Response;

class PublicEventPolicy extends PublicBasePolicy
{
    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(): bool|Response
    {
        return $this->canViewPublic();
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(): bool|Response
    {
        return $this->canViewPublic();
    }

    /**
     * Determine whether the user can register for the event.
     */
    public function register(?PublicUser $user, PublicEvent $event): bool|Response
    {
        $authCheck = $this->canPerformAuthenticatedAction($user, 'vous inscrire à cet événement');
        if ($authCheck !== true) {
            return $authCheck;
        }

        return $this->checkEventRegistrationRules($event);
    }

    /**
     * Check event-specific registration rules.
     */
    private function checkEventRegistrationRules(PublicEvent $event): bool|Response
    {
        if ($event->start_date <= now()) {
            return $this->deny('Les inscriptions sont fermées pour cet événement.');
        }

        if ($event->max_participants && $event->registrations_count >= $event->max_participants) {
            return $this->deny('Cet événement affiche complet.');
        }

        return true;
    }

    /**
     * Determine whether the user can cancel their registration.
     */
    public function cancelRegistration(?PublicUser $user, PublicEvent $event): bool|Response
    {
        $authCheck = $this->canPerformAuthenticatedAction($user, 'annuler votre inscription');
        if ($authCheck !== true) {
            return $authCheck;
        }

        if ($event->start_date <= now()->addHours(24)) {
            return $this->deny('Vous ne pouvez plus annuler votre inscription moins de 24h avant l\'événement.');
        }

        return true;
    }
}
