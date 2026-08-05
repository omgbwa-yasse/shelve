<?php

namespace App\Policies;

use App\Models\Record;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux notices (domaine D02).
 *
 * Adaptée au modèle unifié `Record` (les `RecordPhysical`/`RecordDigital*` ont été
 * fusionnés dans `records` le 2026-08-04) : le typage est porté sur `Record`, les
 * préfixes de permission restent `records_*` (identiques à ceux du PermissionSeeder
 * et aux Gate Blade `records_view`/`records_create`…).
 *
 * L'isolation par organisation n'est PAS portée ici : elle est appliquée dans les
 * contrôleurs par `Record::inOrganisation()->findOrFail()` (404 hors org, motif D03).
 */
class RecordPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'records_view');
    }

    public function view(?User $user, Record $record): bool|Response
    {
        return $this->canView($user, $record, 'records_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'records_create');
    }

    public function update(?User $user, Record $record): bool|Response
    {
        return $this->canUpdate($user, $record, 'records_update');
    }

    public function delete(?User $user, Record $record): bool|Response
    {
        return $this->canDelete($user, $record, 'records_delete');
    }

    public function restore(?User $user, Record $record): bool|Response
    {
        return $this->canUpdate($user, $record, 'records_update');
    }

    public function forceDelete(?User $user, Record $record): bool|Response
    {
        return $this->canForceDelete($user, $record, 'records_force_delete');
    }

    /**
     * Marquage de statut (action non-CRUD la plus simple) : requiert la mise à jour.
     */
    public function status(?User $user, Record $record): bool|Response
    {
        return $this->canUpdate($user, $record, 'records_update');
    }

    /**
     * Réactivation (demande de retour à un statut antérieur) : requiert la mise à jour.
     */
    public function reactivate(?User $user, Record $record): bool|Response
    {
        return $this->canUpdate($user, $record, 'records_update');
    }
}
