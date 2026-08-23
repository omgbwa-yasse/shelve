<?php

namespace App\Services;

use App\Models\Record;
use Carbon\CarbonImmutable;

/**
 * Calcule le cycle de vie d'une notice à partir de sa classe, de la règle de
 * conservation appliquée à cette classe et de ses dates métier.
 *
 * La communicabilité est volontairement séparée du transfert : elle indique
 * quand le document devient consultable, pas quand il doit changer de service.
 */
class RecordLifecycleService
{
    public function analyse(Record $record): array
    {
        $record->loadMissing([
            'activity.retentions.sort',
            'activity.communicability',
        ]);

        $issues = [];
        $activity = $record->activity;
        $retentions = $activity?->retentions ?? collect();

        if (! $activity) {
            $issues[] = 'Aucune classe du plan de classement n’est affectée.';
        } elseif ($retentions->isEmpty()) {
            $issues[] = 'Aucune règle de conservation n’est affectée à cette classe.';
        } elseif ($retentions->count() > 1) {
            $issues[] = 'Plusieurs règles sont affectées à cette classe : une seule règle effective est requise.';
        }

        $referenceDate = $this->referenceDate($record);
        if (! $referenceDate) {
            $issues[] = 'Aucune date de référence exploitable n’est renseignée.';
        }

        $retention = $retentions->count() === 1 ? $retentions->first() : null;
        $deadline = $retention && $referenceDate
            ? $referenceDate->addYears((int) $retention->duration)
            : null;

        $communicability = $activity?->communicability;
        $communicableFrom = $communicability && $referenceDate
            ? $referenceDate->addYears((int) $communicability->duration)
            : null;

        $isDue = $deadline?->endOfDay()->isPast() ?? false;
        $sortCode = $retention?->sort?->code;
        $phase = $this->phase($record, $issues, $isDue, $sortCode);

        return [
            'phase' => $phase,
            'phase_label' => $this->phaseLabel($phase),
            'issues' => $issues,
            'activity' => $activity,
            'retention' => $retention,
            'sort' => $retention?->sort,
            'reference_date' => $referenceDate,
            'deadline' => $deadline,
            'is_due' => $isDue,
            'communicability' => $communicability,
            'communicable_from' => $communicableFrom,
        ];
    }

    public function referenceDate(Record $record): ?CarbonImmutable
    {
        $value = $record->closing_date
            ?? $record->end_date
            ?? $record->date_exact
            ?? $record->opening_date
            ?? $record->start_date;

        return $value ? CarbonImmutable::parse($value) : null;
    }

    private function phase(Record $record, array $issues, bool $isDue, ?string $sortCode): string
    {
        if ($record->destruction_effective_date) {
            return 'eliminated';
        }

        if ($record->deposit_effective_date) {
            return 'deposited';
        }

        if ($issues !== []) {
            return 'configuration_required';
        }

        if ($isDue) {
            return match ($sortCode) {
                'C' => 'permanent_conservation_due',
                'T' => 'appraisal_due',
                'E' => 'elimination_due',
                default => 'final_action_due',
            };
        }

        if ($record->closing_date && ! $record->transfer_effective_date) {
            return 'transfer_due';
        }

        if ($record->transfer_effective_date) {
            return 'intermediate_conservation';
        }

        return 'active';
    }

    private function phaseLabel(string $phase): string
    {
        return match ($phase) {
            'active' => 'Dossier actif',
            'transfer_due' => 'Transfert à préparer',
            'intermediate_conservation' => 'Conservation intermédiaire',
            'permanent_conservation_due' => 'Conservation définitive à verser',
            'appraisal_due' => 'Tri à réaliser',
            'elimination_due' => 'Élimination à soumettre',
            'deposited' => 'Versé et conservé définitivement',
            'eliminated' => 'Éliminé avec traçabilité',
            'configuration_required' => 'Configuration à corriger',
            default => 'Action finale à déterminer',
        };
    }
}
