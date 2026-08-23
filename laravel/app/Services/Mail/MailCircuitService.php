<?php

namespace App\Services\Mail;

use App\Enums\MailStatusEnum;
use App\Models\Mail;
use App\Models\MailCircuit;
use App\Models\MailCircuitAction;
use App\Models\MailCircuitStep;
use App\Models\Organisation;
use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MailCircuitService
{
    public function templates(): array
    {
        return config('mail_circuits.templates', []);
    }

    public function start(Mail $mail, string $templateCode, User $initiator, array $data): MailCircuit
    {
        $template = $this->templates()[$templateCode] ?? null;

        if (! $template) {
            throw ValidationException::withMessages(['template_code' => 'Le modèle de circuit sélectionné est introuvable.']);
        }

        if ($mail->circuits()->whereIn('status', [
            MailCircuit::STATUS_ACTIVE,
            MailCircuit::STATUS_BLOCKED,
            MailCircuit::STATUS_REVISION,
        ])->exists()) {
            throw ValidationException::withMessages(['template_code' => 'Ce courrier possède déjà un circuit ouvert. Clôturez-le ou annulez-le avant d’en démarrer un autre.']);
        }

        return DB::transaction(function () use ($mail, $templateCode, $template, $initiator, $data) {
            $metadata = $this->normaliseMetadata($data, $template);
            $definitions = $templateCode === 'custom'
                ? $this->customStepDefinitions($data)
                : ($template['steps'] ?? []);
            $providedValidators = $data['validators'] ?? [];
            foreach ($definitions as $definition) {
                if (isset($definition['user_id'], $definition['validator'])) {
                    $providedValidators[$definition['validator']] = $definition['user_id'];
                }
            }

            $circuit = MailCircuit::create([
                'mail_id' => $mail->id,
                'template_code' => $templateCode,
                'template_name' => $template['name'],
                'status' => MailCircuit::STATUS_ACTIVE,
                'mode' => $templateCode === 'custom' ? ($data['custom_mode'] ?? 'sequential') : $this->detectMode($definitions),
                'version' => 1,
                'metadata' => $metadata,
                'initiated_by' => $initiator->id,
                'started_at' => now(),
            ]);

            $resolved = [];
            $missing = [];

            foreach (array_values($definitions) as $index => $definition) {
                $token = $definition['validator'] ?? 'user';
                $condition = $definition['condition'] ?? null;
                $included = $this->conditionMatches($condition, $metadata);
                $assignee = $included
                    ? $this->resolveValidator($token, $mail, $initiator, $providedValidators, $resolved)
                    : null;

                if ($included && ! $assignee) {
                    $missing[$token] = 'Choisissez un valideur pour l’étape « '.($definition['label'] ?? $token).' ».';
                }

                if ($assignee) {
                    $resolved[$token] = $assignee;
                }

                MailCircuitStep::create([
                    'mail_circuit_id' => $circuit->id,
                    'step_key' => $definition['key'] ?? ('step_'.($index + 1)),
                    'label' => $definition['label'] ?? 'Validation',
                    'stage' => (int) ($definition['stage'] ?? ($index + 1)),
                    'sequence' => $index + 1,
                    'validator_type' => $token === 'user' ? 'user' : 'function',
                    'validator_value' => $token,
                    'assigned_user_id' => $assignee?->id,
                    'original_assigned_user_id' => $assignee?->id,
                    'status' => $included ? MailCircuitStep::STATUS_WAITING : MailCircuitStep::STATUS_SKIPPED,
                    'is_required' => (bool) ($definition['required'] ?? true),
                    'is_parallel' => (bool) ($definition['parallel'] ?? false),
                    'condition_key' => $condition,
                    'due_at' => null,
                ]);
            }

            if ($missing !== []) {
                throw ValidationException::withMessages(
                    collect($missing)->mapWithKeys(fn ($message, $token) => ["validators.$token" => $message])->all()
                );
            }

            $restricted = (bool) ($template['restricted'] ?? false) || (bool) ($metadata['sensitive'] ?? false);
            $mail->update([
                'status' => MailStatusEnum::PENDING_REVIEW,
                'access_restricted' => $restricted,
                'confidentiality_level' => $restricted ? 'confidential' : ($mail->confidentiality_level ?? 'normal'),
            ]);

            $this->record($circuit, null, 'started', null, MailCircuit::STATUS_ACTIVE, $initiator, $data['note'] ?? null, [
                'template_code' => $templateCode,
            ]);
            $mail->logAction('circuit_started', 'status', null, MailStatusEnum::PENDING_REVIEW->value, 'Circuit démarré : '.$template['name']);

            if ($circuit->steps()->where('status', '!=', MailCircuitStep::STATUS_SKIPPED)->doesntExist()) {
                $this->complete($circuit, $initiator);
            } else {
                $this->activateNextStage($circuit, null);
            }

            return $circuit->fresh(['mail', 'steps.assignedUser', 'actions.actor']);
        });
    }

    public function act(MailCircuitStep $step, string $action, User $actor, ?string $comment = null): MailCircuit
    {
        return DB::transaction(function () use ($step, $action, $actor, $comment) {
            $step->loadMissing('circuit.mail');
            $circuit = $step->circuit;

            if (! $step->isActionable()) {
                throw ValidationException::withMessages(['action' => 'Cette étape n’est plus en attente de décision.']);
            }

            $from = $step->status;

            if (in_array($action, ['approve', 'approve_with_comment'], true)) {
                $step->update([
                    'status' => MailCircuitStep::STATUS_APPROVED,
                    'acted_by' => $actor->id,
                    'acted_at' => now(),
                    'comment' => $comment,
                ]);
                $this->record($circuit, $step, $action, $from, MailCircuitStep::STATUS_APPROVED, $actor, $comment);
                $this->advanceAfterDecision($circuit, $step->stage, $actor);
            } elseif ($action === 'revision') {
                if (! trim((string) $comment)) {
                    throw ValidationException::withMessages(['comment' => 'Précisez les corrections demandées.']);
                }

                $step->update([
                    'status' => MailCircuitStep::STATUS_REVISION,
                    'acted_by' => $actor->id,
                    'acted_at' => now(),
                    'comment' => $comment,
                ]);
                $this->cancelRemainingSteps($circuit, $step->id);
                $circuit->update(['status' => MailCircuit::STATUS_REVISION]);
                $circuit->mail->update(['status' => MailStatusEnum::REJECTED]);
                $this->record($circuit, $step, 'revision_requested', $from, MailCircuitStep::STATUS_REVISION, $actor, $comment);
                $circuit->mail->logAction('circuit_revision_requested', 'status', null, MailStatusEnum::REJECTED->value, $comment);
            } elseif ($action === 'reject') {
                if (! trim((string) $comment)) {
                    throw ValidationException::withMessages(['comment' => 'Le rejet doit être motivé.']);
                }

                $step->update([
                    'status' => MailCircuitStep::STATUS_REJECTED,
                    'acted_by' => $actor->id,
                    'acted_at' => now(),
                    'comment' => $comment,
                ]);
                $this->cancelRemainingSteps($circuit, $step->id);
                $circuit->update(['status' => MailCircuit::STATUS_REJECTED, 'rejected_at' => now()]);
                $circuit->mail->update(['status' => MailStatusEnum::REJECTED]);
                $this->record($circuit, $step, 'rejected', $from, MailCircuitStep::STATUS_REJECTED, $actor, $comment);
                $circuit->mail->logAction('circuit_rejected', 'status', null, MailStatusEnum::REJECTED->value, $comment);
            } elseif ($action === 'abstain') {
                if (! trim((string) $comment)) {
                    throw ValidationException::withMessages(['comment' => 'Expliquez le motif de l’abstention.']);
                }

                $step->update([
                    'status' => MailCircuitStep::STATUS_ABSTAINED,
                    'acted_by' => $actor->id,
                    'acted_at' => now(),
                    'comment' => $comment,
                ]);
                $circuit->update(['status' => MailCircuit::STATUS_BLOCKED]);
                $this->record($circuit, $step, 'abstained', $from, MailCircuitStep::STATUS_ABSTAINED, $actor, $comment);
            } else {
                throw ValidationException::withMessages(['action' => 'Décision inconnue.']);
            }

            return $circuit->fresh(['mail', 'steps.assignedUser', 'actions.actor']);
        });
    }

    public function delegate(MailCircuitStep $step, User $newAssignee, User $actor, string $reason): MailCircuit
    {
        return $this->reassign($step, $newAssignee, $actor, $reason, 'delegated');
    }

    public function escalate(MailCircuitStep $step, User $newAssignee, User $actor, string $reason): MailCircuit
    {
        return $this->reassign($step, $newAssignee, $actor, $reason, 'escalated');
    }

    private function reassign(MailCircuitStep $step, User $newAssignee, User $actor, string $reason, string $action): MailCircuit
    {
        if (! trim($reason)) {
            throw ValidationException::withMessages(['reason' => 'Le motif est obligatoire.']);
        }

        return DB::transaction(function () use ($step, $newAssignee, $actor, $reason, $action) {
            $step->loadMissing('circuit.mail');
            $fromUserId = $step->assigned_user_id;
            $fromStatus = $step->status;

            if (! in_array($fromStatus, [
                MailCircuitStep::STATUS_PENDING,
                MailCircuitStep::STATUS_EXPIRED,
                MailCircuitStep::STATUS_ABSTAINED,
            ], true)) {
                throw ValidationException::withMessages(['assignee_id' => 'Cette étape ne peut plus être réaffectée.']);
            }

            $step->update([
                'assigned_user_id' => $newAssignee->id,
                'delegated_from_user_id' => $fromUserId,
                'status' => MailCircuitStep::STATUS_PENDING,
                'acted_by' => null,
                'acted_at' => null,
                'comment' => null,
                'due_at' => $this->dueAtFor($step->circuit, null),
            ]);
            $step->circuit->update(['status' => MailCircuit::STATUS_ACTIVE]);

            $this->record($step->circuit, $step, $action, $fromStatus, MailCircuitStep::STATUS_PENDING, $actor, $reason, [
                'from_user_id' => $fromUserId,
                'to_user_id' => $newAssignee->id,
            ]);

            return $step->circuit->fresh(['mail', 'steps.assignedUser', 'actions.actor']);
        });
    }

    public function resumeAfterRevision(MailCircuit $circuit, User $actor, ?string $comment = null): MailCircuit
    {
        if ($circuit->status !== MailCircuit::STATUS_REVISION) {
            throw ValidationException::withMessages(['comment' => 'Ce circuit n’est pas en attente de correction.']);
        }

        return DB::transaction(function () use ($circuit, $actor, $comment) {
            $version = $circuit->version + 1;
            $metadata = $circuit->metadata ?? [];

            foreach ($circuit->steps as $step) {
                $included = $this->conditionMatches($step->condition_key, $metadata);
                $step->update([
                    'status' => $included ? MailCircuitStep::STATUS_WAITING : MailCircuitStep::STATUS_SKIPPED,
                    'acted_by' => null,
                    'acted_at' => null,
                    'comment' => null,
                    'due_at' => null,
                ]);
            }

            $circuit->update([
                'status' => MailCircuit::STATUS_ACTIVE,
                'version' => $version,
                'completed_by' => null,
                'completed_at' => null,
                'rejected_at' => null,
            ]);
            $circuit->mail->update(['status' => MailStatusEnum::PENDING_REVIEW]);
            $this->record($circuit, null, 'resubmitted', MailCircuit::STATUS_REVISION, MailCircuit::STATUS_ACTIVE, $actor, $comment);
            $circuit->mail->logAction('circuit_resubmitted', 'status', MailStatusEnum::REJECTED->value, MailStatusEnum::PENDING_REVIEW->value, $comment ?? 'Courrier corrigé et resoumis');
            $this->activateNextStage($circuit, null);

            return $circuit->fresh(['mail', 'steps.assignedUser', 'actions.actor']);
        });
    }

    public function cancel(MailCircuit $circuit, User $actor, string $reason): MailCircuit
    {
        if (! $circuit->isOpen()) {
            throw ValidationException::withMessages(['reason' => 'Ce circuit est déjà clôturé.']);
        }

        return DB::transaction(function () use ($circuit, $actor, $reason) {
            $from = $circuit->status;
            $circuit->steps()->whereIn('status', [
                MailCircuitStep::STATUS_WAITING,
                MailCircuitStep::STATUS_PENDING,
                MailCircuitStep::STATUS_EXPIRED,
                MailCircuitStep::STATUS_ABSTAINED,
            ])->update(['status' => MailCircuitStep::STATUS_CANCELLED]);
            $circuit->update(['status' => MailCircuit::STATUS_CANCELLED, 'cancelled_at' => now()]);
            $circuit->mail->update(['status' => MailStatusEnum::DRAFT]);
            $this->record($circuit, null, 'cancelled', $from, MailCircuit::STATUS_CANCELLED, $actor, $reason);
            $circuit->mail->logAction('circuit_cancelled', 'status', null, MailStatusEnum::DRAFT->value, $reason);

            return $circuit->fresh(['mail', 'steps.assignedUser', 'actions.actor']);
        });
    }

    public function transmit(MailCircuit $circuit, User $actor, ?string $comment = null): MailCircuit
    {
        if ($circuit->status !== MailCircuit::STATUS_COMPLETED) {
            throw ValidationException::withMessages(['comment' => 'Toutes les validations doivent être terminées avant la transmission.']);
        }

        $circuit->mail->update([
            'status' => MailStatusEnum::TRANSMITTED,
            'processed_at' => now(),
        ]);
        $this->record($circuit, null, 'transmitted', MailCircuit::STATUS_COMPLETED, MailCircuit::STATUS_COMPLETED, $actor, $comment);
        $circuit->mail->logAction('circuit_transmitted', 'status', MailStatusEnum::APPROVED->value, MailStatusEnum::TRANSMITTED->value, $comment ?? 'Courrier validé puis transmis');

        return $circuit->fresh(['mail', 'steps.assignedUser', 'actions.actor']);
    }

    public function refreshExpired(MailCircuit $circuit): MailCircuit
    {
        $expired = $circuit->steps()
            ->where('status', MailCircuitStep::STATUS_PENDING)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->get();

        foreach ($expired as $step) {
            $step->update(['status' => MailCircuitStep::STATUS_EXPIRED]);
            $this->record($circuit, $step, 'expired', MailCircuitStep::STATUS_PENDING, MailCircuitStep::STATUS_EXPIRED, null, 'Échéance dépassée : relance, délégation ou escalade requise.');
        }

        if ($expired->isNotEmpty() && $circuit->status === MailCircuit::STATUS_ACTIVE) {
            $circuit->update(['status' => MailCircuit::STATUS_BLOCKED]);
        }

        return $circuit->fresh(['mail', 'steps.assignedUser', 'actions.actor']);
    }

    private function advanceAfterDecision(MailCircuit $circuit, int $stage, User $actor): void
    {
        $stageSteps = $circuit->steps()->where('stage', $stage)->get();
        $blocking = $stageSteps->contains(function (MailCircuitStep $step) {
            if (in_array($step->status, [MailCircuitStep::STATUS_APPROVED, MailCircuitStep::STATUS_SKIPPED], true)) {
                return false;
            }

            return ! (! $step->is_required && $step->status === MailCircuitStep::STATUS_ABSTAINED);
        });

        if ($blocking) {
            return;
        }

        $this->activateNextStage($circuit, $stage, $actor);
    }

    private function activateNextStage(MailCircuit $circuit, ?int $afterStage, ?User $actor = null): void
    {
        $query = $circuit->steps()->where('status', MailCircuitStep::STATUS_WAITING);
        if ($afterStage !== null) {
            $query->where('stage', '>', $afterStage);
        }

        $nextStage = $query->min('stage');
        if ($nextStage === null) {
            $this->complete($circuit, $actor ?? $circuit->initiator);

            return;
        }

        $steps = $circuit->steps()->where('stage', $nextStage)->where('status', MailCircuitStep::STATUS_WAITING)->get();
        if ($steps->isEmpty()) {
            $this->activateNextStage($circuit, (int) $nextStage, $actor);

            return;
        }

        foreach ($steps as $step) {
            $step->update([
                'status' => MailCircuitStep::STATUS_PENDING,
                'due_at' => $this->dueAtFor($circuit, $step),
            ]);
        }

        $circuit->update(['status' => MailCircuit::STATUS_ACTIVE]);
        $this->record($circuit, null, 'stage_activated', null, MailCircuit::STATUS_ACTIVE, $actor, 'Étape '.(int) $nextStage.' ouverte'.($steps->count() > 1 ? ' en parallèle' : ''), [
            'stage' => (int) $nextStage,
            'step_ids' => $steps->pluck('id')->all(),
        ]);
    }

    private function complete(MailCircuit $circuit, ?User $actor): void
    {
        $circuit->update([
            'status' => MailCircuit::STATUS_COMPLETED,
            'completed_by' => $actor?->id,
            'completed_at' => now(),
        ]);
        $circuit->mail->update(['status' => MailStatusEnum::APPROVED]);
        $this->record($circuit, null, 'completed', MailCircuit::STATUS_ACTIVE, MailCircuit::STATUS_COMPLETED, $actor, 'Toutes les validations obligatoires sont terminées.');
        $circuit->mail->logAction('circuit_completed', 'status', MailStatusEnum::PENDING_REVIEW->value, MailStatusEnum::APPROVED->value, 'Circuit entièrement validé');
    }

    private function cancelRemainingSteps(MailCircuit $circuit, int $exceptStepId): void
    {
        $circuit->steps()
            ->whereKeyNot($exceptStepId)
            ->whereIn('status', [MailCircuitStep::STATUS_WAITING, MailCircuitStep::STATUS_PENDING, MailCircuitStep::STATUS_EXPIRED, MailCircuitStep::STATUS_ABSTAINED])
            ->update(['status' => MailCircuitStep::STATUS_CANCELLED]);
    }

    private function record(MailCircuit $circuit, ?MailCircuitStep $step, string $action, ?string $from, ?string $to, ?User $actor, ?string $comment = null, array $metadata = []): void
    {
        MailCircuitAction::create([
            'mail_circuit_id' => $circuit->id,
            'mail_circuit_step_id' => $step?->id,
            'version' => $circuit->version,
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'actor_id' => $actor?->id,
            'comment' => $comment,
            'metadata' => $metadata === [] ? null : $metadata,
            'created_at' => now(),
        ]);
    }

    private function normaliseMetadata(array $data, array $template): array
    {
        return [
            'amount' => isset($data['amount']) ? (float) $data['amount'] : null,
            'financial_threshold' => (float) config('mail_circuits.financial_threshold', 5000000),
            'finance_required' => (bool) ($data['finance_required'] ?? false),
            'legal_required' => (bool) ($data['legal_required'] ?? false),
            'dsi_required' => (bool) ($data['dsi_required'] ?? false),
            'dg_required' => (bool) ($data['dg_required'] ?? false),
            'official_response' => (bool) ($data['official_response'] ?? false),
            'sensitive' => (bool) ($data['sensitive'] ?? ($template['restricted'] ?? false)),
            'urgent' => (bool) ($data['urgent'] ?? ($template['urgent'] ?? false)),
            'note' => $data['note'] ?? null,
        ];
    }

    private function customStepDefinitions(array $data): array
    {
        $users = array_values(array_filter(array_map('intval', $data['custom_validators'] ?? [])));
        if ($users === []) {
            throw ValidationException::withMessages(['custom_validators' => 'Choisissez au moins un valideur pour le circuit personnalisé.']);
        }

        $mode = ($data['custom_mode'] ?? 'sequential') === 'parallel' ? 'parallel' : 'sequential';
        $labels = $data['custom_labels'] ?? [];

        return collect($users)->take(4)->values()->map(function (int $userId, int $index) use ($mode, $labels) {
            return [
                'key' => 'custom_'.($index + 1),
                'label' => trim((string) ($labels[$index] ?? '')) ?: 'Validation personnalisée '.($index + 1),
                'stage' => $mode === 'parallel' ? 1 : $index + 1,
                'validator' => 'custom_'.($index + 1),
                'parallel' => $mode === 'parallel',
                'user_id' => $userId,
            ];
        })->all();
    }

    private function resolveValidator(string $token, Mail $mail, User $initiator, array $provided, array $resolved): ?User
    {
        $providedId = Arr::get($provided, $token);
        if ($providedId) {
            return User::find((int) $providedId);
        }

        return match ($token) {
            'n1' => $mail->sender?->hierarchicalSuperior($mail->sender_organisation_id),
            'n2' => ($resolved['n1'] ?? null)?->hierarchicalSuperior(($resolved['n1'] ?? null)?->current_organisation_id),
            'dg' => $this->findDg(),
            'service_manager' => $mail->recipientOrganisation?->responsible($mail->activity_id)
                ?? $mail->senderOrganisation?->responsible($mail->activity_id),
            'finance' => $this->findFunctionalUser(['finance', 'financière', 'financiere', 'comptabilité', 'comptabilite', 'budget']),
            'legal' => $this->findFunctionalUser(['juridique', 'legal', 'contentieux']),
            'dsi' => $this->findFunctionalUser(['informatique', 'système', 'systeme', 'numérique', 'numerique', 'dsi']),
            'purchase' => $this->findFunctionalUser(['achat', 'approvisionnement', 'marché', 'marche']),
            default => null,
        };
    }

    private function findDg(): ?User
    {
        $dgRoleId = DB::table('roles')->where('name', 'DG')->value('id');
        if ($dgRoleId) {
            $userId = UserOrganisationRole::where('role_id', $dgRoleId)->value('user_id');
            if ($userId) {
                return User::find($userId);
            }
        }

        $root = Organisation::whereNull('parent_id')->first();

        return $root?->responsible() ?? $root?->users()->first();
    }

    private function findFunctionalUser(array $keywords): ?User
    {
        $organisation = Organisation::query()
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($keyword).'%'])
                        ->orWhereRaw('LOWER(code) LIKE ?', ['%'.mb_strtolower($keyword).'%']);
                }
            })
            ->first();

        return $organisation?->responsible() ?? $organisation?->users()->first();
    }

    private function conditionMatches(?string $condition, array $metadata): bool
    {
        if (! $condition) {
            return true;
        }

        return match ($condition) {
            'finance_required' => (bool) ($metadata['finance_required'] ?? false),
            'legal_required' => (bool) ($metadata['legal_required'] ?? false),
            'dsi_required' => (bool) ($metadata['dsi_required'] ?? false),
            'financial_threshold_or_dg' => (bool) ($metadata['dg_required'] ?? false)
                || ((float) ($metadata['amount'] ?? 0) >= (float) ($metadata['financial_threshold'] ?? PHP_FLOAT_MAX)),
            'official_or_dg' => (bool) ($metadata['official_response'] ?? false) || (bool) ($metadata['dg_required'] ?? false),
            default => true,
        };
    }

    private function detectMode(array $definitions): string
    {
        return collect($definitions)->groupBy('stage')->contains(fn ($steps) => count($steps) > 1)
            ? 'hybrid'
            : 'sequential';
    }

    private function dueAtFor(MailCircuit $circuit, ?MailCircuitStep $step): \Carbon\Carbon
    {
        $urgent = (bool) ($circuit->metadata['urgent'] ?? false);
        $days = $urgent ? 0 : (int) config('mail_circuits.default_due_days', 3);

        if ($days === 0) {
            return now()->addHours(4);
        }

        return now()->addWeekdays($days)->endOfDay();
    }
}
