<?php

namespace App\Policies;

use App\Models\ExternalContact;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux contacts externes (référentiel D01).
 *
 * Créée lors du portage de D01 : `ExternalContactController` n'appliquait aucune
 * autorisation côté Blade (risque R04).
 */
class ExternalContactPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'external_contact_viewAny');
    }

    public function view(?User $user, ExternalContact $contact): bool|Response
    {
        return $this->canView($user, $contact, 'external_contact_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'external_contact_create');
    }

    public function update(?User $user, ExternalContact $contact): bool|Response
    {
        return $this->canUpdate($user, $contact, 'external_contact_update');
    }

    public function delete(?User $user, ExternalContact $contact): bool|Response
    {
        return $this->canDelete($user, $contact, 'external_contact_delete');
    }

    public function forceDelete(?User $user, ExternalContact $contact): bool|Response
    {
        return $this->canForceDelete($user, $contact, 'external_contact_force_delete');
    }
}
