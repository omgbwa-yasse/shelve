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
            'type' => [
                'required',
                Rule::enum(WorkflowStepType::class),
            ],
            // Accept raw JSON string or array and decode below
            'configuration' => 'nullable',
            'estimated_duration' => 'nullable|integer|min:0',
            'is_required' => 'boolean',
            'can_be_skipped' => 'boolean',
            // Accept raw JSON string or array and decode below
            'conditions' => 'nullable',
            'assignments' => 'nullable|array',
            'assignments.*.assignee_type' => 'required|string',
            'assignments.*.assignee_id' => 'nullable|exists:users,id',
            'assignments.*.organisation_id' => 'nullable|exists:organisations,id',
            'assignments.*.role' => 'nullable|string',
        ]);

        // Uniformiser la conversion des champs JSON
        $configuration = null;
        if ($request->filled('configuration')) {
            $rawConfig = $request->input('configuration');
            if (is_array($rawConfig)) {
                $configuration = $rawConfig;
            } else {
                $configuration = json_decode($rawConfig, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withInput()->withErrors(['configuration' => __('Le champ configuration doit être un JSON valide.')]);
                }
            }
        }

        $conditions = null;
        if ($request->filled('conditions')) {
            $rawCond = $request->input('conditions');
            if (is_array($rawCond)) {
                $conditions = $rawCond;
            } else {
                $conditions = json_decode($rawCond, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withInput()->withErrors(['conditions' => __('Le champ conditions doit être un JSON valide.')]);
                }
            }
        }

        // Validation des assignations
        if (!empty($validated['assignments'])) {
            foreach ($validated['assignments'] as $i => $assignment) {
                if (empty($assignment['assignee_type']) || !in_array($assignment['assignee_type'], ['user', 'organisation'])) {
                    return back()->withInput()->withErrors(["assignments.$i.assignee_type" => __('Type d’assigné invalide')]);
                }
                if ($assignment['assignee_type'] === 'user' && empty($assignment['assignee_id'])) {
                    return back()->withInput()->withErrors(["assignments.$i.assignee_id" => __('Utilisateur assigné requis')]);
                }
                if ($assignment['assignee_type'] === 'organisation' && empty($assignment['organisation_id'])) {
                    return back()->withInput()->withErrors(["assignments.$i.organisation_id" => __('Organisation assignée requise')]);
                }
            }
        }

        // Réordonner les étapes existantes si nécessaire
        $orderIndex = $validated['order_index'];
        $template->steps()
            ->where('order_index', '>=', $orderIndex)
            ->increment('order_index');

        // Créer l'étape
        $step = new WorkflowStep([
            'workflow_template_id' => $template->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'order_index' => $orderIndex,
            'step_type' => $validated['type'],
            'configuration' => $configuration,
            'estimated_duration' => $validated['estimated_duration'] ?? null,
            'is_required' => $validated['is_required'] ?? true,
            'can_be_skipped' => $validated['can_be_skipped'] ?? false,
            'conditions' => $conditions,
        ]);

        $step->save();

        // Créer les assignations
        if (!empty($validated['assignments'])) {
            foreach ($validated['assignments'] as $assignmentData) {
                $assignment = new WorkflowStepAssignment([
                    'assignee_type' => $assignmentData['assignee_type'],
                    'assignee_user_id' => $assignmentData['assignee_id'] ?? null,
                    'assignee_organisation_id' => $assignmentData['organisation_id'] ?? null,
                    'assignment_rules' => null,
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
            // Accept raw JSON string or array and decode below
            'configuration' => 'nullable',
            'estimated_duration' => 'nullable|integer|min:0',
            'is_required' => 'boolean',
            'can_be_skipped' => 'boolean',
            // Accept raw JSON string or array and decode below
            'conditions' => 'nullable',
            'assignments' => 'nullable|array',
            'assignments.*.assignee_type' => 'required|string',
            'assignments.*.assignee_id' => 'nullable|exists:users,id',
            'assignments.*.organisation_id' => 'nullable|exists:organisations,id',
            'assignments.*.role' => 'nullable|string',
            'assignments.*.assignment_id' => 'nullable|exists:workflow_step_assignments,id',
        ]);

        // Decode JSON fields if they are strings
        $configuration = null;
        if ($request->filled('configuration')) {
            if (is_array($request->input('configuration'))) {
                $configuration = $request->input('configuration');
            } else {
                $configuration = json_decode($request->input('configuration'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withInput()->withErrors(['configuration' => __('Configuration JSON invalide')]);
                }
            }
        }

        $conditions = null;
        if ($request->filled('conditions')) {
            if (is_array($request->input('conditions'))) {
                $conditions = $request->input('conditions');
            } else {
                $conditions = json_decode($request->input('conditions'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withInput()->withErrors(['conditions' => __('Conditions JSON invalides')]);
                }
            }
        }

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
            'configuration' => $configuration,
            'estimated_duration' => $validated['estimated_duration'] ?? null,
            'is_required' => $validated['is_required'] ?? true,
            'can_be_skipped' => $validated['can_be_skipped'] ?? false,
            'conditions' => $conditions,
        ]);

        // Mettre à jour les assignations existantes et créer les nouvelles
        $existingIds = $step->assignments->pluck('id')->toArray();
        $updatedIds = [];

        if (!empty($validated['assignments'])) {
            foreach ($validated['assignments'] as $assignmentData) {
                $assignmentId = $assignmentData['assignment_id'] ?? null;

                if ($assignmentId) {
                    $assignment = WorkflowStepAssignment::find($assignmentId);
                    $assignment->update([
                        'assignee_type' => $assignmentData['assignee_type'],
                        'assignee_user_id' => $assignmentData['assignee_id'] ?? null,
                        'assignee_organisation_id' => $assignmentData['organisation_id'] ?? null,
                        'assignment_rules' => $assignmentData['role'] ? ['role' => $assignmentData['role']] : null,
                    ]);
                    $updatedIds[] = $assignment->id;
                } else {
                    $assignment = new WorkflowStepAssignment([
                        'assignee_type' => $assignmentData['assignee_type'],
                        'assignee_user_id' => $assignmentData['assignee_id'] ?? null,
                        'assignee_organisation_id' => $assignmentData['organisation_id'] ?? null,
                        'assignment_rules' => $assignmentData['role'] ? ['role' => $assignmentData['role']] : null,
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
        if ($step->instances()->whereHas('workflowInstance', function ($query) {
            $query->where('status', '!=', 'completed');
        })->exists()) {
            return redirect()
                ->route('workflows.steps.show', [$template, $step])
                ->with('error', 'Impossible de supprimer cette étape car elle est utilisée dans des workflows actifs.');
        }

        // Blocage si étape critique (étape unique du workflow)
        $totalSteps = $template->steps()->count();
        if ($totalSteps <= 1) {
            return redirect()
                ->route('workflows.steps.show', [$template, $step])
                ->with('error', 'Impossible de supprimer l’étape : c’est l’unique étape du workflow.');
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
