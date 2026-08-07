<?php

namespace App\Observers;

use App\Models\Slip;
use App\Models\SlipStatus;

class SlipObserver
{
    /**
     * Handle the Slip "saving" event.
     * Met à jour automatiquement le slip_status_id en fonction des booléens
     */
    public function saving(Slip $slip): void
    {
        // Si le slip_status_id est déjà défini manuellement, ne pas le modifier
        if ($slip->isDirty('slip_status_id') && $slip->slip_status_id !== null) {
            return;
        }

        // Déterminer le statut en fonction des booléens
        $statusName = $this->determineStatus($slip);

        // Récupérer l'ID du statut correspondant
        $status = SlipStatus::where('name', $statusName)->first();

        if ($status) {
            $slip->slip_status_id = $status->id;
        }
    }

    /**
     * Détermine le nom du statut en fonction des booléens
     */
    private function determineStatus(Slip $slip): string
    {
        if ($slip->is_integrated) {
            return 'Integrated';
        }

        if ($slip->is_approved) {
            return 'Approved';
        }

        if ($slip->is_received) {
            return 'Received';
        }

        return 'Projects';
    }

    /**
     * Handle the Slip "created" event.
     */
    public function created(Slip $slip): void
    {
        //
    }

    /**
     * Handle the Slip "updated" event.
     */
    public function updated(Slip $slip): void
    {
        //
    }

    /**
     * Handle the Slip "deleted" event.
     */
    public function deleted(Slip $slip): void
    {
        //
    }

    /**
     * Handle the Slip "restored" event.
     */
    public function restored(Slip $slip): void
    {
        //
    }

    /**
     * Handle the Slip "force deleted" event.
     */
    public function forceDeleted(Slip $slip): void
    {
        //
    }
}
