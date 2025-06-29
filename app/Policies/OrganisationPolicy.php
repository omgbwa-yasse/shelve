<?php

namespace App\Policies;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class OrganisationPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Gestion des organisations accessible aux superadmins et utilisateurs avec permission spécifique
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'organisations.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Organisation $organisation): bool|Response
    {
        return $this->canView($user, $organisation, 'organisations.view');
    }

    /**
     * Determine whether the user can create models.
     * Création d'organisations généralement limitée aux superadmins
     */
    public function create(?User $user): bool|Response
    {
        if (!$user) {
            return $this->deny('Vous devez être connecté pour créer une organisation.');
        }

        // Seuls les superadmins peuvent créer de nouvelles organisations
        if (!Gate::forUser($user)->allows('is-superadmin')) {
            return $this->deny('Seul un superadmin peut créer de nouvelles organisations.');
        }

        return $this->canCreate($user, 'organisations.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Organisation $organisation): bool|Response
    {
        if (!$user) {
            return $this->deny('Vous devez être connecté pour modifier une organisation.');
        }

        // Les superadmins peuvent modifier toutes les organisations
        if (Gate::forUser($user)->allows('is-superadmin')) {
            return $this->allow();
        }

        // Les autres utilisateurs ne peuvent modifier que leur organisation courante
        if ($this->userHasCurrentOrganisation($user) &&
            isset($user->current_organisation_id) &&
            $user->current_organisation_id === $organisation->id) {
            return $this->canUpdate($user, $organisation, 'organisations.update');
        }

        return $this->deny('Vous ne pouvez modifier que votre organisation courante.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Organisation $organisation): bool|Response
    {
        if (!$user) {
            return $this->deny('Vous devez être connecté pour supprimer une organisation.');
        }

        // Seuls les superadmins peuvent supprimer des organisations
        if (!Gate::forUser($user)->allows('is-superadmin')) {
            return $this->deny('Seul un superadmin peut supprimer des organisations.');
        }

        // Empêcher la suppression de l'organisation "Direction générale"
        if ($organisation->code === 'DIR-GEN') {
            return $this->deny('L\'organisation "Direction générale" ne peut pas être supprimée.');
        }

        return $this->canDelete($user, $organisation, 'organisations.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Organisation $organisation): bool|Response
    {
        // Seuls les superadmins peuvent restaurer des organisations
        if (!Gate::forUser($user)->allows('is-superadmin')) {
            return $this->deny('Seul un superadmin peut restaurer des organisations.');
        }

        return $this->canUpdate($user, $organisation, 'organisations.update');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Organisation $organisation): bool|Response
    {
        // Seuls les superadmins peuvent faire un force delete
        if (!Gate::forUser($user)->allows('is-superadmin')) {
            return $this->deny('Seul un superadmin peut supprimer définitivement des organisations.');
        }

        // Empêcher la suppression définitive de l'organisation "Direction générale"
        if ($organisation->code === 'DIR-GEN') {
            return $this->deny('L\'organisation "Direction générale" ne peut pas être supprimée définitivement.');
        }

        return $this->canForceDelete($user, $organisation, 'organisations.force_delete');
    }
}
