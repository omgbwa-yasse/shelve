<?php

namespace App\Services;

use App\Models\RecordArtifact;
use App\Models\RecordArtifactExhibition;
use App\Models\RecordArtifactLoan;
use App\Models\RecordArtifactConditionReport;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service de gestion des artifacts de musée (Phase 6)
 *
 * Fonctionnalités:
 * - Catalogue d'objets de musée
 * - Gestion des expositions
 * - Système de prêts
 * - Rapports de conservation
 * - Évaluation et assurance
 */
class RecordArtifactService
{
    /**
     * Créer un artifact
     */
    public function createArtifact(
        array $data,
        User $creator,
        Organisation $organisation
    ): RecordArtifact {
        DB::beginTransaction();
        try {
            // Générer le code si non fourni
            if (!isset($data['code'])) {
                $data['code'] = $this->generateCode($organisation);
            }

            // Le code sera auto-généré par le modèle (ART-YYYY-NNNN)
            $artifact = RecordArtifact::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'sub_category' => $data['sub_category'] ?? null,
                'material' => $data['material'] ?? null,
                'technique' => $data['technique'] ?? null,

                // Dimensions
                'height' => $data['height'] ?? null,
                'width' => $data['width'] ?? null,
                'depth' => $data['depth'] ?? null,
                'weight' => $data['weight'] ?? null,
                'dimensions_notes' => $data['dimensions_notes'] ?? null,

                // Origine et datation
                'origin' => $data['origin'] ?? null,
                'period' => $data['period'] ?? null,
                'date_start' => $data['date_start'] ?? null,
                'date_end' => $data['date_end'] ?? null,
                'date_precision' => $data['date_precision'] ?? null,

                // Auteur/Créateur
                'author' => $data['author'] ?? null,
                'author_role' => $data['author_role'] ?? null,
                'author_birth_date' => $data['author_birth_date'] ?? null,
                'author_death_date' => $data['author_death_date'] ?? null,

                // Acquisition
                'acquisition_method' => $data['acquisition_method'] ?? null,
                'acquisition_date' => $data['acquisition_date'] ?? null,
                'acquisition_price' => $data['acquisition_price'] ?? null,
                'acquisition_source' => $data['acquisition_source'] ?? null,

                // Conservation
                'conservation_state' => $data['conservation_state'] ?? 'good',
                'conservation_notes' => $data['conservation_notes'] ?? null,
                'last_conservation_check' => $data['last_conservation_check'] ?? null,
                'next_conservation_check' => $data['next_conservation_check'] ?? null,

                // Localisation
                'current_location' => $data['current_location'] ?? null,
                'storage_location' => $data['storage_location'] ?? null,
                'is_on_display' => false,
                'is_on_loan' => false,

                // Valeurs
                'estimated_value' => $data['estimated_value'] ?? null,
                'insurance_value' => $data['insurance_value'] ?? null,
                'valuation_date' => $data['valuation_date'] ?? null,

                // Statut et métadonnées
                'status' => $data['status'] ?? 'active',
                'metadata' => $data['metadata'] ?? [],

                // Audit
                'creator_id' => $creator->id,
                'organisation_id' => $organisation->id,
            ]);

            DB::commit();
            return $artifact;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour un artifact
     */
    public function updateArtifact(RecordArtifact $artifact, array $data): RecordArtifact
    {
        DB::beginTransaction();
        try {
            $artifact->update($data);

            DB::commit();
            return $artifact->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Ajouter un artifact à une exposition
     */
    public function addToExhibition(
        RecordArtifact $artifact,
        array $data,
        bool $isCurrent = true
    ): RecordArtifactExhibition {
        DB::beginTransaction();
        try {
            // Créer l'exposition
            $exhibition = RecordArtifactExhibition::create([
                'artifact_id' => $artifact->id,
                'exhibition_name' => $data['exhibition_name'],
                'venue' => $data['venue'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'is_current' => $isCurrent,
                'notes' => $data['notes'] ?? null,
            ]);

            // Si exposition actuelle, mettre à jour l'artifact
            if ($isCurrent) {
                // Fermer les autres expositions actives
                RecordArtifactExhibition::where('artifact_id', $artifact->id)
                    ->where('id', '!=', $exhibition->id)
                    ->update(['is_current' => false]);

                $artifact->update(['is_on_display' => true]);
            }

            DB::commit();
            return $exhibition;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Retirer un artifact d'une exposition
     */
    public function removeFromExhibition(
        RecordArtifact $artifact,
        ?RecordArtifactExhibition $exhibition = null
    ): bool {
        DB::beginTransaction();
        try {
            if ($exhibition) {
                $exhibition->update(['is_current' => false]);
            } else {
                // Fermer toutes les expositions actives
                RecordArtifactExhibition::where('artifact_id', $artifact->id)
                    ->update(['is_current' => false]);
            }

            // Mettre à jour l'artifact
            $artifact->update(['is_on_display' => false]);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Prêter un artifact
     */
    public function loanArtifact(
        RecordArtifact $artifact,
        array $data
    ): RecordArtifactLoan {
        DB::beginTransaction();
        try {
            // Vérifier que l'artifact peut être prêté
            if ($artifact->is_on_loan) {
                throw new Exception("L'artifact est déjà en prêt");
            }

            if ($artifact->is_on_display) {
                throw new Exception("L'artifact est actuellement exposé");
            }

            if (in_array($artifact->conservation_state, ['poor', 'critical'])) {
                throw new Exception("L'état de conservation ne permet pas le prêt");
            }

            // Créer le prêt
            $loan = RecordArtifactLoan::create([
                'artifact_id' => $artifact->id,
                'borrower_name' => $data['borrower_name'],
                'borrower_contact' => $data['borrower_contact'] ?? null,
                'loan_date' => $data['loan_date'] ?? now()->toDateString(),
                'return_date' => $data['return_date'],
                'actual_return_date' => null,
                'status' => 'active',
                'conditions' => $data['conditions'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Mettre à jour l'artifact
            $artifact->update([
                'is_on_loan' => true,
                'status' => 'on_loan',
            ]);

            DB::commit();
            return $loan;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Retourner un artifact de prêt
     */
    public function returnFromLoan(
        RecordArtifact $artifact,
        ?array $data = []
    ): RecordArtifactLoan {
        DB::beginTransaction();
        try {
            // Récupérer le prêt actif
            $loan = RecordArtifactLoan::where('artifact_id', $artifact->id)
                ->where('status', 'active')
                ->first();

            if (!$loan) {
                throw new Exception("Aucun prêt actif trouvé pour cet artifact");
            }

            // Mettre à jour le prêt
            $loan->update([
                'actual_return_date' => $data['actual_return_date'] ?? now()->toDateString(),
                'status' => 'returned',
                'notes' => isset($data['notes'])
                    ? ($loan->notes ? $loan->notes . "\n" . $data['notes'] : $data['notes'])
                    : $loan->notes,
            ]);

            // Mettre à jour l'artifact
            $artifact->update([
                'is_on_loan' => false,
                'status' => 'active',
            ]);

            DB::commit();
            return $loan;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Ajouter un rapport de conservation
     */
    public function addConditionReport(
        RecordArtifact $artifact,
        array $data,
        User $inspector
    ): RecordArtifactConditionReport {
        DB::beginTransaction();
        try {
            // Créer le rapport
            $report = RecordArtifactConditionReport::create([
                'artifact_id' => $artifact->id,
                'report_date' => $data['report_date'] ?? now()->toDateString(),
                'overall_condition' => $data['overall_condition'],
                'observations' => $data['observations'],
                'recommendations' => $data['recommendations'] ?? null,
                'inspector_id' => $inspector->id,
            ]);

            // Mettre à jour l'artifact
            $artifact->update([
                'conservation_state' => $data['overall_condition'],
                'conservation_notes' => $data['observations'],
                'last_conservation_check' => $report->report_date,
                'next_conservation_check' => $data['next_conservation_check'] ?? null,
            ]);

            // Si état critique, changer le statut
            if ($data['overall_condition'] === 'critical') {
                $artifact->update(['status' => 'in_restoration']);
            }

            DB::commit();
            return $report;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour l'évaluation d'un artifact
     */
    public function updateValuation(
        RecordArtifact $artifact,
        float $estimatedValue,
        float $insuranceValue,
        ?string $valuationDate = null
    ): RecordArtifact {
        DB::beginTransaction();
        try {
            $artifact->update([
                'estimated_value' => $estimatedValue,
                'insurance_value' => $insuranceValue,
                'valuation_date' => $valuationDate ?? now()->toDateString(),
            ]);

            DB::commit();
            return $artifact->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Rechercher des artifacts
     */
    public function searchArtifacts(array $filters)
    {
        $query = RecordArtifact::query();

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['conservation_state'])) {
            $query->where('conservation_state', $filters['conservation_state']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_on_display'])) {
            $query->where('is_on_display', $filters['is_on_display']);
        }

        if (isset($filters['is_on_loan'])) {
            $query->where('is_on_loan', $filters['is_on_loan']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('description', 'LIKE', "%{$filters['search']}%")
                  ->orWhere('author', 'LIKE', "%{$filters['search']}%");
            });
        }

        if (isset($filters['organisation_id'])) {
            $query->where('organisation_id', $filters['organisation_id']);
        }

        return $query->with(['exhibitions', 'loans', 'conditionReports'])
                     ->orderBy('created_at', 'desc')
                     ->get();
    }

    /**
     * Obtenir les artifacts nécessitant une vérification de conservation
     */
    public function getArtifactsNeedingConservationCheck(): \Illuminate\Database\Eloquent\Collection
    {
        return RecordArtifact::whereNotNull('next_conservation_check')
            ->where('next_conservation_check', '<=', now())
            ->where('status', 'active')
            ->orderBy('next_conservation_check', 'asc')
            ->get();
    }

    /**
     * Obtenir les artifacts en état critique
     */
    public function getCriticalArtifacts(): \Illuminate\Database\Eloquent\Collection
    {
        return RecordArtifact::where('conservation_state', 'critical')
            ->orWhere(function ($query) {
                $query->where('conservation_state', 'poor')
                      ->whereNull('next_conservation_check');
            })
            ->orderBy('conservation_state', 'desc')
            ->orderBy('last_conservation_check', 'asc')
            ->get();
    }

    /**
     * Obtenir les prêts actifs
     */
    public function getActiveLoans(): \Illuminate\Database\Eloquent\Collection
    {
        return RecordArtifactLoan::where('status', 'active')
            ->with('artifact')
            ->orderBy('return_date', 'asc')
            ->get();
    }

    /**
     * Obtenir les prêts en retard
     */
    public function getOverdueLoans(): \Illuminate\Database\Eloquent\Collection
    {
        return RecordArtifactLoan::where('status', 'active')
            ->where('return_date', '<', now())
            ->with('artifact')
            ->orderBy('return_date', 'asc')
            ->get();
    }

    /**
     * Obtenir les expositions en cours
     */
    public function getCurrentExhibitions(): \Illuminate\Database\Eloquent\Collection
    {
        return RecordArtifactExhibition::where('is_current', true)
            ->with('artifact')
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Obtenir les statistiques des artifacts
     */
    public function getStatistics(?int $organisationId = null): array
    {
        $query = RecordArtifact::query();

        if ($organisationId) {
            $query->where('organisation_id', $organisationId);
        }

        $total = $query->count();
        $onDisplay = (clone $query)->where('is_on_display', true)->count();
        $onLoan = (clone $query)->where('is_on_loan', true)->count();
        $inRestoration = (clone $query)->where('status', 'in_restoration')->count();

        $byConservation = (clone $query)
            ->select('conservation_state', DB::raw('count(*) as count'))
            ->groupBy('conservation_state')
            ->pluck('count', 'conservation_state')
            ->toArray();

        $byCategory = (clone $query)
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $totalValue = (clone $query)->sum('estimated_value');
        $totalInsuranceValue = (clone $query)->sum('insurance_value');

        return [
            'total' => $total,
            'on_display' => $onDisplay,
            'on_loan' => $onLoan,
            'in_restoration' => $inRestoration,
            'in_storage' => $total - $onDisplay - $onLoan - $inRestoration,
            'by_conservation' => $byConservation,
            'by_category' => $byCategory,
            'total_value' => $totalValue,
            'total_insurance_value' => $totalInsuranceValue,
            'needs_conservation_check' => $this->getArtifactsNeedingConservationCheck()->count(),
            'critical_state' => ($byConservation['critical'] ?? 0) + ($byConservation['poor'] ?? 0),
        ];
    }

    /**
     * Générer un code unique pour un artifact (ART-YYYY-NNNN)
     */
    private function generateCode(Organisation $organisation): string
    {
        $year = date('Y');
        $prefix = 'ART-' . $year . '-';

        // Trouver le dernier numéro de l'année
        $lastArtifact = RecordArtifact::where('organisation_id', $organisation->id)
            ->where('code', 'LIKE', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($lastArtifact) {
            $lastNumber = (int) substr($lastArtifact->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
