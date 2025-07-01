<?php

namespace App\Http\Controllers;

use App\Enums\WorkflowStepType;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStepAssignment;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WorkflowStepController extends Controller
{
    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher les étapes d'un template (rediriger vers le template)
     */
    public function index(WorkflowTemplate $template)
    {
        return redirect()->route('workflows.templates.show', $template);
    }

    /**
     * Afficher le formulaire de création d'une étape
     */
    public function create(WorkflowTemplate $template)
    {
        $this->authorize('update', $template);

        $stepTypes = WorkflowStepType::forSelect();
        $users = User::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();
        $maxOrder = $template->steps()->max('order_index') ?? 0;

        return view('workflow.steps.create', compact('template', 'stepTypes', 'users', 'organisations', 'maxOrder'));
    }

    /**
     * Enregistrer une nouvelle étape de workflow
     */
    public function store(Request $request, WorkflowTemplate $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'order_index' => 'required|integer|min:0',
            'step_type' => [
                'required',
                Rule::enum(WorkflowStepType::class),
            ],
            'configuration' => 'nullable|array',
            'estimated_duration' => 'nullable|integer|min:0',
            'is_required' => 'boolean',
            'can_be_skipped' => 'boolean',
            'conditions' => 'nullable|array',
            'assignee_type.*' => 'required|string',
            'assignee_user_id.*' => 'nullable|exists:users,id',
            'assignee_organisation_id.*' => 'nullable|exists:organisations,id',
            'assignment_rules.*' => 'nullable|array',
        ]);

        // Réordonner les étapes existantes si nécessaire
        $orderIndex = $validated['order_index'];
        $template->steps()
            ->where('order_index', '>=', $orderIndex)
            ->increment('order_index');

        // Créer l'étape
        $step = new WorkflowStep([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'order_index' => $orderIndex,
            'step_type' => $validated['step_type'],
            'configuration' => $validated['configuration'] ?? null,
            'estimated_duration' => $validated['estimated_duration'] ?? null,
            'is_required' => $validated['is_required'] ?? true,
            'can_be_skipped' => $validated['can_be_skipped'] ?? false,
            'conditions' => $validated['conditions'] ?? null,
        ]);

        $template->steps()->save($step);

        // Créer les assignations
        if (!empty($validated['assignee_type'])) {
            foreach ($validated['assignee_type'] as $key => $type) {
                $assignment = new WorkflowStepAssignment([
                    'assignee_type' => $type,
                    'assignee_user_id' => $validated['assignee_user_id'][$key] ?? null,
                    'assignee_organisation_id' => $validated['assignee_organisation_id'][$key] ?? null,
                    'assignment_rules' => $validated['assignment_rules'][$key] ?? null,
                    'allow_reassignment' => true,
                ]);

                $step->assignments()->save($assignment);
            }
        }

        return redirect()
            ->route('workflows.templates.show', $template)
            ->with('success', 'L\'étape a été ajoutée au workflow avec succès.');
    }

    /**
     * Afficher une étape de workflow spécifique
     */
    public function show(WorkflowTemplate $template, WorkflowStep $step)
    {
        $this->authorize('view', $template);

        $step->load('assignments.user', 'assignments.organisation');

        return view('workflow.steps.show', compact('template', 'step'));
    }

    /**
     * Afficher le formulaire de modification d'une étape
     */
    public function edit(WorkflowTemplate $template, WorkflowStep $step)
    {
        $this->authorize('update', $template);

        $stepTypes = WorkflowStepType::forSelect();
        $users = User::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();
        $step->load('assignments');

        return view('workflow.steps.edit', compact('template', 'step', 'stepTypes', 'users', 'organisations'));
    }

    /**
     * Mettre à jour une étape de workflow
     */
    public function update(Request $request, WorkflowTemplate $template, WorkflowStep $step)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'order_index' => 'required|integer|min:0',
            'step_type' => [
                'required',
                Rule::enum(WorkflowStepType::class),
            ],
            'configuration' => 'nullable|array',
            'estimated_duration' => 'nullable|integer|min:0',
            'is_required' => 'boolean',
            'can_be_skipped' => 'boolean',
            'conditions' => 'nullable|array',
            'assignee_type.*' => 'required|string',
            'assignee_user_id.*' => 'nullable|exists:users,id',
            'assignee_organisation_id.*' => 'nullable|exists:organisations,id',
            'assignment_rules.*' => 'nullable|array',
            'assignment_id.*' => 'nullable|exists:workflow_step_assignments,id',
        ]);

        // Gérer la réorganisation
        if ($validated['order_index'] != $step->order_index) {
            if ($validated['order_index'] > $step->order_index) {
                $template->steps()
                    ->where('order_index', '>', $step->order_index)
                    ->where('order_index', '<=', $validated['order_index'])
                    ->decrement('order_index');
            } else {
                $template->steps()
                    ->where('order_index', '>=', $validated['order_index'])
                    ->where('order_index', '<', $step->order_index)
                    ->increment('order_index');
            }
        }

        // Mettre à jour l'étape
        $step->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'order_index' => $validated['order_index'],
            'step_type' => $validated['step_type'],
            'configuration' => $validated['configuration'] ?? null,
            'estimated_duration' => $validated['estimated_duration'] ?? null,
            'is_required' => $validated['is_required'] ?? true,
            'can_be_skipped' => $validated['can_be_skipped'] ?? false,
            'conditions' => $validated['conditions'] ?? null,
        ]);

        // Mettre à jour les assignations existantes et créer les nouvelles
        $existingIds = $step->assignments->pluck('id')->toArray();
        $updatedIds = [];

        if (!empty($validated['assignee_type'])) {
            foreach ($validated['assignee_type'] as $key => $type) {
                $assignmentId = $validated['assignment_id'][$key] ?? null;

                if ($assignmentId) {
                    $assignment = WorkflowStepAssignment::find($assignmentId);
                    $assignment->update([
                        'assignee_type' => $type,
                        'assignee_user_id' => $validated['assignee_user_id'][$key] ?? null,
                        'assignee_organisation_id' => $validated['assignee_organisation_id'][$key] ?? null,
                        'assignment_rules' => $validated['assignment_rules'][$key] ?? null,
                    ]);
                    $updatedIds[] = $assignment->id;
                } else {
                    $assignment = new WorkflowStepAssignment([
                        'assignee_type' => $type,
                        'assignee_user_id' => $validated['assignee_user_id'][$key] ?? null,
                        'assignee_organisation_id' => $validated['assignee_organisation_id'][$key] ?? null,
                        'assignment_rules' => $validated['assignment_rules'][$key] ?? null,
                        'allow_reassignment' => true,
                    ]);

                    $step->assignments()->save($assignment);
                    $updatedIds[] = $assignment->id;
                }
            }
        }

        // Supprimer les assignations qui ne sont plus présentes
        $toDelete = array_diff($existingIds, $updatedIds);
        if (!empty($toDelete)) {
            WorkflowStepAssignment::whereIn('id', $toDelete)->delete();
        }

        return redirect()
            ->route('workflows.steps.show', [$template, $step])
            ->with('success', 'L\'étape a été mise à jour avec succès.');
    }

    /**
     * Supprimer une étape de workflow
     */
    public function destroy(WorkflowTemplate $template, WorkflowStep $step)
    {
        $this->authorize('update', $template);

        // Vérifier si cette étape est utilisée dans des workflows actifs
        if ($step->stepInstances()->whereHas('workflowInstance', function ($query) {
            $query->where('status', '!=', 'completed');
        })->exists()) {
            return redirect()
                ->route('workflows.steps.show', [$template, $step])
                ->with('error', 'Impossible de supprimer cette étape car elle est utilisée dans des workflows actifs.');
        }

        // Récupérer l'ordre pour réorganiser
        $orderIndex = $step->order_index;

        // Supprimer l'étape et ses assignations (cascade)
        $step->delete();

        // Réordonner les étapes suivantes
        $template->steps()
            ->where('order_index', '>', $orderIndex)
            ->decrement('order_index');

        return redirect()
            ->route('workflows.templates.show', $template)
            ->with('success', 'L\'étape a été supprimée avec succès.');
    }

    /**
     * Réordonner les étapes
     */
    public function reorder(Request $request, WorkflowTemplate $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'steps' => 'required|array',
            'steps.*' => 'exists:workflow_steps,id',
        ]);

        $steps = $validated['steps'];

        foreach ($steps as $index => $stepId) {
            WorkflowStep::where('id', $stepId)
                ->where('workflow_template_id', $template->id)
                ->update(['order_index' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
