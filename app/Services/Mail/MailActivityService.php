<?php

namespace App\Services\Mail;

use App\Models\Activity;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Activités du plan de classement proposées à la saisie d'un courrier.
 *
 * Rattacher un courrier à une activité n'est pas un confort de classement : c'est
 * ce qui rend le cloisonnement des intérims opérant. Un volet d'intérim porte une
 * activité, et `OrganisationInterim::coversActivity()` renvoie false dès que le
 * courrier n'en a aucune — autrement dit, un courrier sans activité confié à une
 * direction dont le responsable est absent n'est traitable par PERSONNE.
 *
 * Toutes les entités ne portent pas d'activités (les services rattachés n'en ont
 * généralement pas) : on remonte donc l'organigramme jusqu'à en trouver.
 */
class MailActivityService
{
    /**
     * Activités proposables à cet utilisateur, dans l'ordre alphabétique.
     *
     * @return Collection<int, Activity>
     */
    public function optionsFor(User $user): Collection
    {
        $organisation = $user->current_organisation_id
            ? Organisation::find($user->current_organisation_id)
            : null;

        return $this->optionsForOrganisation($organisation);
    }

    /**
     * @return Collection<int, Activity>
     */
    public function optionsForOrganisation(?Organisation $organisation): Collection
    {
        $current = $organisation;
        $guard = 0;

        // L'entité elle-même, puis ses parents : un service hérite du plan de
        // classement de sa direction.
        while ($current && $guard < 10) {
            $activities = $current->activities()->orderBy('name')->get();

            if ($activities->isNotEmpty()) {
                return $activities;
            }

            $current = $current->parent;
            $guard++;
        }

        // Aucune entité de la branche ne porte d'activité : on propose le plan
        // complet plutôt que de laisser un champ vide.
        return Activity::orderBy('name')->get();
    }
}
