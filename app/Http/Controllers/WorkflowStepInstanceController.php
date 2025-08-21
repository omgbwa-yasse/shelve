<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstance;
use App\Models\WorkflowStepInstance;
use App\Models\User;
use App\Models\Organisation;
use App\Enums\WorkflowStepInstanceStatus;
use App\Enums\AssignmentType;
use App\Enums\WorkflowInstanceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class WorkflowStepInstanceController extends Controller
{
    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher le détail d'une étape de workflow
     */
    public function show(WorkflowInstance $instance, WorkflowStepInstance $stepInstance)
    {
        $this->authorize('view', $instance);

        if ($stepInstance->workflow_instance_id !== $instance->id) {
            abort(404);
        }

        $stepInstance->load(['step', 'workflowInstance.template', 'assignedUser', 'assignedOrganisation']);

        return view('workflow.step-instances.show', compact('instance', 'stepInstance'));
    }

    /**
     * Afficher le formulaire d'édition d'une étape de workflow
     */
    public function edit(WorkflowInstance $instance, WorkflowStepInstance $stepInstance)
    {
        $this->authorize('update', $instance);

        if ($stepInstance->workflow_instance_id !== $instance->id) {
            abort(404);
        }

        if ($instance->status !== WorkflowInstanceStatus::IN_PROGRESS) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes de workflows en cours peuvent être modifiées.');
        }

        $stepInstance->load(['step', 'workflowInstance.template', 'assignedUser', 'assignedOrganisation']);
        $users = User::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();
        $assignmentTypes = AssignmentType::forSelect();
        $statuses = WorkflowStepInstanceStatus::forSelect();

        return view('workflow.step-instances.edit', compact(
            'instance',
            'stepInstance',
            'users',
            'organisations',
            'assignmentTypes',
            'statuses'
        ));
    }

    /**
     * Mettre à jour une étape de workflow
     */
    public function update(Request $request, WorkflowInstance $instance, WorkflowStepInstance $stepInstance)
    {
        $this->authorize('update', $instance);

        if ($stepInstance->workflow_instance_id !== $instance->id) {
            abort(404);
        }

        if ($instance->status !== WorkflowInstanceStatus::IN_PROGRESS) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes de workflows en cours peuvent être modifiées.');
        }

        $validated = $request->validate([
            'status' => [new Enum(WorkflowStepInstanceStatus::class)],
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'assigned_to_organisation_id' => 'nullable|exists:organisations,id',
            'due_date' => 'nullable|date',
            'failure_reason' => 'nullable|string|required_if:status,failed',
        ]);

        // Gérer les états de l'étape
        $oldStatus = $stepInstance->status;
        $newStatus = WorkflowStepInstanceStatus::from($validated['status']);

        // Mettre à jour les dates en fonction du changement de statut
        if ($oldStatus !== $newStatus) {
            if ($newStatus === WorkflowStepInstanceStatus::IN_PROGRESS && !$stepInstance->started_at) {
                $stepInstance->started_at = now();
            }

            if (in_array($newStatus, [WorkflowStepInstanceStatus::COMPLETED, WorkflowStepInstanceStatus::SKIPPED, WorkflowStepInstanceStatus::FAILED]) && !$stepInstance->completed_at) {
                $stepInstance->completed_at = now();
            }
        }

        // Mettre à jour l'étape
        $stepInstance->update([
            'status' => $validated['status'],
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'assigned_to_organisation_id' => $validated['assigned_to_organisation_id'] ?? null,
            'due_date' => $validated['due_date'] ?? $stepInstance->due_date,
            'failure_reason' => $validated['failure_reason'] ?? $stepInstance->failure_reason,
        ]);

        // Si l'étape actuelle est terminée, passer à la suivante dans le workflow
        if ($oldStatus !== WorkflowStepInstanceStatus::COMPLETED && $newStatus === WorkflowStepInstanceStatus::COMPLETED &&
            $instance->current_step_id === $stepInstance->workflow_step_id) {

            $this->moveToNextStep($instance, $stepInstance);
        }

        return redirect()
            ->route('workflows.step-instances.show', [$instance, $stepInstance])
            ->with('success', 'L\'étape a été mise à jour avec succès.');
    }

    /**
     * Démarrer une étape
     */
    public function start(WorkflowInstance $instance, WorkflowStepInstance $stepInstance)
    {
        $this->authorize('update', $instance);

        if ($stepInstance->workflow_instance_id !== $instance->id) {
            abort(404);
        }

        if ($instance->status !== WorkflowInstanceStatus::IN_PROGRESS) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes de workflows en cours peuvent être démarrées.');
        }

        if ($stepInstance->status !== WorkflowStepInstanceStatus::PENDING) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes en attente peuvent être démarrées.');
        }

        $stepInstance->update([
            'status' => WorkflowStepInstanceStatus::IN_PROGRESS,
            'started_at' => now(),
        ]);

        // Si cette étape n'est pas l'étape courante, mettre à jour l'étape courante du workflow
        if ($instance->current_step_id !== $stepInstance->workflow_step_id) {
            $instance->update([
                'current_step_id' => $stepInstance->workflow_step_id,
            ]);
        }

        return redirect()
            ->route('workflows.step-instances.show', [$instance, $stepInstance])
            ->with('success', 'L\'étape a été démarrée avec succès.');
    }

    /**
     * Terminer une étape
     */
    public function complete(Request $request, WorkflowInstance $instance, WorkflowStepInstance $stepInstance)
    {
        $this->authorize('update', $instance);

        if ($stepInstance->workflow_instance_id !== $instance->id) {
            abort(404);
        }

        if ($instance->status !== WorkflowInstanceStatus::IN_PROGRESS) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes de workflows en cours peuvent être terminées.');
        }

        if ($stepInstance->status !== WorkflowStepInstanceStatus::IN_PROGRESS) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes en cours peuvent être terminées.');
        }

        $validated = $request->validate([
            'output_data' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $stepInstance->update([
            'status' => WorkflowStepInstanceStatus::COMPLETED,
            'completed_at' => now(),
            'output_data' => $validated['output_data'] ?? $stepInstance->output_data,
            'notes' => $validated['notes'] ?? $stepInstance->notes,
        ]);

        // Si cette étape est l'étape courante, passer à la suivante
        if ($instance->current_step_id === $stepInstance->workflow_step_id) {
            $this->moveToNextStep($instance, $stepInstance);
        }

        return redirect()
            ->route('workflows.step-instances.show', [$instance, $stepInstance])
            ->with('success', 'L\'étape a été terminée avec succès.');
    }

    /**
     * Ignorer une étape
     */
    public function skip(Request $request, WorkflowInstance $instance, WorkflowStepInstance $stepInstance)
    {
        $this->authorize('update', $instance);

        if ($stepInstance->workflow_instance_id !== $instance->id) {
            abort(404);
        }

        if ($instance->status !== WorkflowInstanceStatus::IN_PROGRESS) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes de workflows en cours peuvent être ignorées.');
        }

        if (!in_array($stepInstance->status, [WorkflowStepInstanceStatus::PENDING, WorkflowStepInstanceStatus::IN_PROGRESS])) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes en attente ou en cours peuvent être ignorées.');
        }

        // Vérifier si l'étape peut être ignorée
        if (!$stepInstance->step->can_be_skipped) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Cette étape ne peut pas être ignorée.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $stepInstance->update([
            'status' => WorkflowStepInstanceStatus::SKIPPED,
            'completed_at' => now(),
            'notes' => $validated['notes'] ?? $stepInstance->notes,
        ]);

        // Si cette étape est l'étape courante, passer à la suivante
        if ($instance->current_step_id === $stepInstance->workflow_step_id) {
            $this->moveToNextStep($instance, $stepInstance);
        }

        return redirect()
            ->route('workflows.step-instances.show', [$instance, $stepInstance])
            ->with('success', 'L\'étape a été ignorée avec succès.');
    }

    /**
     * Signaler une étape comme échouée
     */
    public function fail(Request $request, WorkflowInstance $instance, WorkflowStepInstance $stepInstance)
    {
        $this->authorize('update', $instance);

        if ($stepInstance->workflow_instance_id !== $instance->id) {
            abort(404);
        }

        if ($instance->status !== WorkflowInstanceStatus::IN_PROGRESS) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes de workflows en cours peuvent être marquées comme échouées.');
        }

        if (!in_array($stepInstance->status, [WorkflowStepInstanceStatus::PENDING, WorkflowStepInstanceStatus::IN_PROGRESS])) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes en attente ou en cours peuvent être marquées comme échouées.');
        }

        $validated = $request->validate([
            'failure_reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $stepInstance->update([
            'status' => WorkflowStepInstanceStatus::FAILED,
            'completed_at' => now(),
            'failure_reason' => $validated['failure_reason'],
            'notes' => $validated['notes'] ?? $stepInstance->notes,
        ]);

        // Mettre le workflow en pause
        $instance->update([
            'status' => WorkflowInstanceStatus::ON_HOLD,
        ]);

        return redirect()
            ->route('workflows.step-instances.show', [$instance, $stepInstance])
            ->with('success', 'L\'étape a été marquée comme échouée et le workflow a été mis en pause.');
    }

    /**
     * Réassigner une étape
     */
    public function reassign(Request $request, WorkflowInstance $instance, WorkflowStepInstance $stepInstance)
    {
        $this->authorize('update', $instance);

        if ($stepInstance->workflow_instance_id !== $instance->id) {
            abort(404);
        }

        if ($instance->status !== WorkflowInstanceStatus::IN_PROGRESS) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes de workflows en cours peuvent être réassignées.');
        }

        if (!in_array($stepInstance->status, [WorkflowStepInstanceStatus::PENDING, WorkflowStepInstanceStatus::IN_PROGRESS])) {
            return redirect()
                ->route('workflows.step-instances.show', [$instance, $stepInstance])
                ->with('error', 'Seules les étapes en attente ou en cours peuvent être réassignées.');
        }

        $validated = $request->validate([
            'assignment_type' => [new Enum(AssignmentType::class)],
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'assigned_to_organisation_id' => 'nullable|exists:organisations,id',
            'assignment_notes' => 'nullable|string',
        ]);

        // Vérifier la cohérence de l'assignation
        if ($validated['assignment_type'] === AssignmentType::USER->value && empty($validated['assigned_to_user_id'])) {
            return redirect()->back()->withErrors(['assigned_to_user_id' => 'Un utilisateur doit être sélectionné pour ce type d\'assignation.']);
        }

        if ($validated['assignment_type'] === AssignmentType::ORGANISATION->value && empty($validated['assigned_to_organisation_id'])) {
            return redirect()->back()->withErrors(['assigned_to_organisation_id' => 'Une organisation doit être sélectionnée pour ce type d\'assignation.']);
        }

        if ($validated['assignment_type'] === AssignmentType::BOTH->value &&
            (empty($validated['assigned_to_user_id']) || empty($validated['assigned_to_organisation_id']))) {
            return redirect()->back()->withErrors(['assignment_type' => 'Un utilisateur et une organisation doivent être sélectionnés pour ce type d\'assignation.']);
        }

        $stepInstance->update([
            'assignment_type' => $validated['assignment_type'],
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'assigned_to_organisation_id' => $validated['assigned_to_organisation_id'] ?? null,
            'assignment_notes' => $validated['assignment_notes'] ?? $stepInstance->assignment_notes,
        ]);

        return redirect()
            ->route('workflows.step-instances.show', [$instance, $stepInstance])
            ->with('success', 'L\'étape a été réassignée avec succès.');
    }

    /**
     * Passer à l'étape suivante dans le workflow
     */
    private function moveToNextStep(WorkflowInstance $instance, WorkflowStepInstance $stepInstance)
    {
        $completedStep = $stepInstance->step;

        // Charger toutes les étapes du template dans l'ordre
        $allSteps = $instance->template->steps()->orderBy('order_index')->get();

        // Trouver l'étape suivante
        $nextStep = null;
        $foundCurrent = false;

        foreach ($allSteps as $step) {
            if ($foundCurrent) {
                $nextStep = $step;
                break;
            }

            if ($step->id === $completedStep->id) {
                $foundCurrent = true;
            }
        }

        // Si une étape suivante existe
        if ($nextStep) {
            // Trouver l'instance de cette étape
            $nextStepInstance = $instance->stepInstances()
                ->where('workflow_step_id', $nextStep->id)
                ->first();

            if ($nextStepInstance) {
                // Mettre à jour l'étape courante du workflow
                $instance->current_step_id = $nextStep->id;
                $instance->save();

                // Mettre à jour le statut de la prochaine étape
                $nextStepInstance->status = WorkflowStepInstanceStatus::PENDING;
                $nextStepInstance->save();
            }
        } else {
            // C'était la dernière étape, marquer le workflow comme terminé
            $instance->status = WorkflowInstanceStatus::COMPLETED;
            $instance->completed_at = now();
            $instance->current_step_id = null;
            $instance->save();
        }
    }
}
