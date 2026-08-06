<?php

namespace App\Policies;

use App\Models\Kpi;
use App\Models\User;
use App\Policies\Concerns\ChecksAttachableAccess;
use Illuminate\Auth\Access\Response;

class KpiPolicy extends BasePolicy
{
    use ChecksAttachableAccess;

    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'kpi_viewAny');
    }

    public function view(?User $user, Kpi $kpi): bool|Response
    {
        $result = $this->canView($user, $kpi, 'kpi_view');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $kpi->attachable_type, $kpi->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'kpi_create');
    }

    public function update(?User $user, Kpi $kpi): bool|Response
    {
        $result = $this->canUpdate($user, $kpi, 'kpi_update');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $kpi->attachable_type, $kpi->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }

    public function delete(?User $user, Kpi $kpi): bool|Response
    {
        $result = $this->canDelete($user, $kpi, 'kpi_delete');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $kpi->attachable_type, $kpi->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }

    /** Enregistrer une mesure — permission dédiée (§2 du plan). */
    public function recordMeasurement(?User $user, Kpi $kpi): bool|Response
    {
        $result = $this->canUpdate($user, $kpi, 'kpi_measurement_create');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $kpi->attachable_type, $kpi->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }
}
