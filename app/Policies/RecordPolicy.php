<?php

namespace App\Policies;

use App\Models\Record;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class RecordPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'records.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Record $record): bool|Response
    {
        return $this->canView($user, $record, 'records.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'records.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Record $record): bool|Response
    {
        // Vérification supplémentaire : un record ne peut être modifié que s'il n'est pas archivé
        if (isset($record->status) && $record->status === 'archived') {
            return $this->deny('Un record archivé ne peut pas être modifié.');
        }

        return $this->canUpdate($user, $record, 'records.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Record $record): bool|Response
    {
        // Vérification supplémentaire : un record ne peut être supprimé que s'il n'est pas en cours de traitement
        if (isset($record->status) && in_array($record->status, ['processing', 'archived'])) {
            return $this->deny('Un record en cours de traitement ou archivé ne peut pas être supprimé.');
        }

        return $this->canDelete($user, $record, 'records.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Record $record): bool|Response
    {
        return $this->canUpdate($user, $record, 'records.update');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Record $record): bool|Response
    {
        // Seuls les superadmins peuvent faire un force delete de records
        if (!Gate::forUser($user)->allows('is-superadmin')) {
            return $this->deny('Seul un superadmin peut supprimer définitivement des records.');
        }

        return $this->canForceDelete($user, $record, 'records.force_delete');
    }

    /**
     * Determine whether the user can archive the model.
     * Méthode custom pour l'archivage des records
     */
    public function archive(User $user, Record $record): bool|Response
    {
        // Vérifier si le record n'est pas déjà archivé
        if (isset($record->status) && $record->status === 'archived') {
            return $this->deny('Ce record est déjà archivé.');
        }

        return $this->canUpdate($user, $record, 'records.archive');
    }
}
