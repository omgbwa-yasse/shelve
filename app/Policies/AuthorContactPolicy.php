<?php

namespace App\Policies;

use App\Models\AuthorContact;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux contacts d'auteurs (référentiel D01).
 *
 * Créée lors du portage de D01 : `AuthorContactController` n'appliquait aucune
 * autorisation côté Blade (risque R04).
 */
class AuthorContactPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'author_contact_viewAny');
    }

    public function view(?User $user, AuthorContact $authorContact): bool|Response
    {
        return $this->canView($user, $authorContact, 'author_contact_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'author_contact_create');
    }

    public function update(?User $user, AuthorContact $authorContact): bool|Response
    {
        return $this->canUpdate($user, $authorContact, 'author_contact_update');
    }

    public function delete(?User $user, AuthorContact $authorContact): bool|Response
    {
        return $this->canDelete($user, $authorContact, 'author_contact_delete');
    }

    public function forceDelete(?User $user, AuthorContact $authorContact): bool|Response
    {
        return $this->canForceDelete($user, $authorContact, 'author_contact_force_delete');
    }
}
