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
     * Supports guest users with optional type-hint.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'records_view');
    }

    /**
     * Determine whether the user can view the model.
     * Supports guest users with optional type-hint.
     */
    public function view(?User $user, Record $record): bool|Response
    {
        return $this->canView($user, $record, 'records_view');
    }

    /**
     * Determine whether the user can create models.
     * Supports guest users with optional type-hint.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'records_create');
    }

    /**
     * Determine whether the user can update the model.
     * Supports guest users with optional type-hint.
     */
    public function update(?User $user, Record $record): bool|Response
    {
        if (!$user) {
            return $this->deny('Vous devez être connecté pour modifier ce record.');
        }

        // Vérification supplémentaire : un record ne peut être modifié que s'il n'est pas archivé
        if (isset($record->status) && $record->status === 'archived') {
            return $this->deny('Un record archivé ne peut pas être modifié.');
        }

        return $this->canUpdate($user, $record, 'records_update');
    }

    /**
     * Determine whether the user can delete the model.
     * Supports guest users with optional type-hint.
     */
    public function delete(?User $user, Record $record): bool|Response
    {
        if (!$user) {
            return $this->deny('Vous devez être connecté pour supprimer ce record.');
        }

        // Vérification supplémentaire : un record ne peut être supprimé que s'il n'est pas en cours de traitement
        if (isset($record->status) && in_array($record->status, ['processing', 'archived'])) {
            return $this->deny('Un record en cours de traitement ou archivé ne peut pas être supprimé.');
        }

        return $this->canDelete($user, $record, 'records_delete');
    }

    /**
     * Determine whether the user can restore the model.
     * Supports guest users with optional type-hint.
     */
    public function restore(?User $user, Record $record): bool|Response
    {
        return $this->canUpdate($user, $record, 'records_update');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Supports guest users with optional type-hint.
     */
    public function forceDelete(?User $user, Record $record): bool|Response
    {
        if (!$user) {
            return $this->deny('Vous devez être connecté pour supprimer définitivement ce record.');
        }

        // Seuls les superadmins peuvent faire un force delete de records
        if (!Gate::forUser($user)->allows('is-superadmin')) {
            return $this->deny('Seul un superadmin peut supprimer définitivement des records.');
        }

        return $this->canForceDelete($user, $record, 'records_force_delete');
    }

    /**
     * Determine whether the user can archive the model.
     * Méthode custom pour l'archivage des records
     * Supports guest users with optional type-hint.
     */
    public function archive(?User $user, Record $record): bool|Response
    {
        if (!$user) {
            return $this->deny('Vous devez être connecté pour archiver ce record.');
        }
        // Vérifier si le record n'est pas déjà archivé
        if (isset($record->status) && $record->status === 'archived') {
            return $this->deny('Ce record est déjà archivé.');
        }

        return $this->canUpdate($user, $record, 'records_archive');
    }
}
