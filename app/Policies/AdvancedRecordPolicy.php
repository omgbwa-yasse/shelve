<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Record;
use Illuminate\Auth\Access\Response;

class AdvancedRecordPolicy extends BasePolicy
{
    /**
     * Abilities that don't require current organisation (empty for Records).
     */
    protected function getGuestAllowedAbilities(): array
    {
        return []; // Records always require organisation context
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'record_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Record $record): bool|Response
    {
        $basicCheck = $this->canView($user, $record, 'record_view');
        if ($basicCheck !== true) {
            return $basicCheck;
        }

        // Additional business rules for viewing records
        return $this->checkRecordVisibilityRules($user, $record);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        $basicCheck = $this->canCreate($user, 'record_create');
        if ($basicCheck !== true) {
            return $basicCheck;
        }

        // Additional business rules for creating records
        return $this->checkRecordCreationRules($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Record $record): bool|Response
    {
        $basicCheck = $this->canUpdate($user, $record, 'record_update');
        if ($basicCheck !== true) {
            return $basicCheck;
        }

        // Additional business rules for updating records
        return $this->checkRecordUpdateRules($user, $record);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Record $record): bool|Response
    {
        $basicCheck = $this->canDelete($user, $record, 'record_delete');
        if ($basicCheck !== true) {
            return $basicCheck;
        }

        // Additional business rules for deleting records
        return $this->checkRecordDeletionRules($user, $record);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Record $record): bool|Response
    {
        return $this->canForceDelete($user, $record, 'record_force_delete');
    }

    /**
     * Determine if user can archive/unarchive records.
     */
    public function archive(?User $user, Record $record): bool|Response
    {
        $result = $this->canUpdate($user, $record, 'record_archive');
        if (!is_bool($result)) {
            return $result;
        }

        if ($record->is_archived) {
            return $this->deny('Ce document est déjà archivé.');
        }

        return $this->allow();
    }

    /**
     * Determine if user can export records.
     */
    public function export(?User $user): bool|Response
    {
        if (!$this->hasPermission($user, 'record_export')) {
            return $this->deny('Vous n\'avez pas la permission d\'exporter des documents.');
        }

        return true;
    }

    /**
     * Check record-specific visibility rules.
     */
    private function checkRecordVisibilityRules(User $user, Record $record): bool|Response
    {
        // Check if record is confidential and user has appropriate clearance
        if ($record->confidentiality_level === 'confidential' && !$user->hasRole('archivist')) {
            return $this->denyAsNotFound();
        }

        // Check if record is in quarantine
        if ($record->status === 'quarantine' && !$user->hasPermissionTo('record_view_quarantine')) {
            return $this->deny('Ce document est en quarantaine.');
        }

        return true;
    }

    /**
     * Check record creation business rules.
     */
    private function checkRecordCreationRules(?User $user): bool|Response
    {
        if (!$user) {
            return true;
        }

        // Check if user has reached their daily creation limit
        $dailyLimit = $user->organisation->settings['daily_record_creation_limit'] ?? null;
        if ($dailyLimit) {
            $todayCount = Record::where('created_by', $user->id)
                ->whereDate('created_at', today())
                ->count();

            if ($todayCount >= $dailyLimit) {
                return $this->deny("Vous avez atteint votre limite quotidienne de création de documents ({$dailyLimit}).");
            }
        }

        return true;
    }

    /**
     * Check record update business rules.
     */
    private function checkRecordUpdateRules(User $user, Record $record): bool|Response
    {
        // Check if record is locked for editing
        if ($record->is_locked && !$user->hasRole('super-admin')) {
            return $this->deny('Ce document est verrouillé et ne peut être modifié.');
        }

        // Check if record is being processed by someone else
        if ($record->processing_by && $record->processing_by !== $user->id) {
            $processor = User::find($record->processing_by);
            return $this->deny("Ce document est actuellement traité par {$processor->name}.");
        }

        return true;
    }

    /**
     * Check record deletion business rules.
     */
    private function checkRecordDeletionRules(User $user, Record $record): bool|Response
    {
        // Check if record has active loans
        if ($record->loans()->active()->exists()) {
            return $this->deny('Ce document ne peut être supprimé car il fait l\'objet de prêts actifs.');
        }

        // Check if record is part of a legal case
        if ($record->legal_holds()->active()->exists()) {
            return $this->deny('Ce document ne peut être supprimé car il fait l\'objet d\'une conservation légale.');
        }

        // Check retention period
        if ($record->retention && $record->retention->end_date > now()) {
            return $this->deny('Ce document ne peut être supprimé avant la fin de sa période de conservation.');
        }

        return true;
    }
}
