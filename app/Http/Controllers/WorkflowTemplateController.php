<?php

namespace App\Http\Controllers;

use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use App\Enums\WorkflowCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WorkflowTemplateController extends Controller
{
    // Constantes pour les règles de validation
    private const NULLABLE_ARRAY = 'nullable|array';
    private const REQUIRED_STRING = 'required|string';
    private const NULLABLE_BOOLEAN = 'nullable|boolean';
    private const NULLABLE_INTEGER_MIN_1 = 'nullable|integer|min:1';
    private const REQUIRED_INTEGER_MIN_1 = 'required|integer|min:1';
    private const NULLABLE_ORG_EXISTS = 'nullable|integer|exists:organisations,id';
    private const REQUIRED_ACTION_EXISTS = 'required|integer|exists:workflow_actions,id';

    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(WorkflowTemplate::class, 'template');
    }

    /**
     * Créer une réponse JSON standardisée pour les erreurs
     */
    private function errorResponse(string $message, $errors = null, int $status = 422)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Créer une réponse JSON standardisée pour les succès
     */
    private function successResponse(string $message, array $data = [])
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Note: Ces méthodes ont été simplifiées suite à la refactorisation du module workflow.
     * Les configurations JSON complexes ont été remplacées par des relations et des champs structurés.
     */
    private function validateConfigurationUniqueness($configuration)
    {
        $errors = [];
        $warnings = [];
        $config = collect($configuration);

        // Vérifier l'unicité des IDs
        $ids = $config->pluck('id');
        if ($ids->count() !== $ids->unique()->count()) {
            $errors[] = 'Des IDs d\'étapes sont dupliqués';
        }

        // Vérifier l'unicité des ordres
        $ordres = $config->pluck('ordre');
        if ($ordres->count() !== $ordres->unique()->count()) {
            $errors[] = 'Des ordres d\'étapes sont dupliqués';
        }

        // Vérifier la continuité des ordres
        $sortedOrdres = $ordres->sort()->values();
        for ($i = 0; $i < $sortedOrdres->count(); $i++) {
            if ($sortedOrdres[$i] !== $i + 1) {
                $warnings[] = 'Les ordres des étapes ne sont pas continus';
                break;
            }
        }

        // Vérifier l'existence des actions et autres champs requis
        foreach ($configuration as $step) {
            $stepId = $step['id'] ?? 'inconnu';

            if (!isset($step['action_id'])) {
                $errors[] = "L'étape '{$stepId}' n'a pas d'action définie";
            }

            if (!isset($step['name']) || empty($step['name'])) {
                $errors[] = "L'étape '{$stepId}' n'a pas de nom défini";
            }

            if (!isset($step['ordre']) || !is_numeric($step['ordre']) || $step['ordre'] < 1) {
                $errors[] = "L'étape '{$stepId}' a un ordre invalide";
            }
        }

        return [$errors, $warnings];
    }

    /**
     * Afficher la liste des templates de workflow
     */
    public function index(Request $request)
    {
        $query = WorkflowTemplate::query();

        // Filtrage par catégorie
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filtrage par statut actif/inactif
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Recherche par nom
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $templates = $query->withCount('steps')
                         ->withCount('instances')
                         ->orderBy('name')
                         ->paginate(10)
                         ->withQueryString();

        return view('workflow.templates.index', compact('templates'));
    }

    /**
     * Afficher le formulaire de création d'un template
     */
    public function create()
    {
        $categories = WorkflowCategory::forSelect();
        return view('workflow.templates.create', compact('categories'));
    }

    /**
     * Enregistrer un nouveau template de workflow
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:workflow_templates',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();

        $template = WorkflowTemplate::create($validated);

        return redirect()
            ->route('workflows.templates.show', $template)
            ->with('success', 'Le template de workflow a été créé avec succès.');
    }

    /**
     * Afficher un template de workflow spécifique
     */
    public function show(WorkflowTemplate $template)
    {
        $template->load(['steps' => function($query) {
            $query->orderBy('order_index');
        }, 'creator', 'steps.assignments']);

        return view('workflow.templates.show', compact('template'));
    }

    /**
     * Afficher le formulaire de modification d'un template
     */
    public function edit(WorkflowTemplate $template)
    {
        $categories = WorkflowCategory::forSelect();
        return view('workflow.templates.edit', compact('template', 'categories'));
    }

    /**
     * Mettre à jour un template de workflow
     */
    public function update(Request $request, WorkflowTemplate $template)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('workflow_templates')->ignore($template->id),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return redirect()
            ->route('workflows.templates.show', $template)
            ->with('success', 'Le template de workflow a été mis à jour avec succès.');
    }

    /**
     * Supprimer un template de workflow
     */
    public function destroy(WorkflowTemplate $template)
    {
        // Vérifier si le template a des instances actives
        if ($template->instances()->where('status', '!=', 'completed')->count() > 0) {
            return redirect()
                ->route('workflows.templates.show', $template)
                ->with('error', 'Impossible de supprimer ce template car il a des instances actives.');
        }

        $template->delete();

        return redirect()
            ->route('workflows.templates.index')
            ->with('success', 'Le template de workflow a été supprimé avec succès.');
    }

    /**
     * Activer ou désactiver un template
     */
    public function toggleActive(WorkflowTemplate $template)
    {
        $template->is_active = !$template->is_active;
        $template->save();

        $status = $template->is_active ? 'activé' : 'désactivé';

        return redirect()
            ->back()
            ->with('success', "Le template de workflow a été {$status} avec succès.");
    }

    /**
     * Dupliquer un template de workflow
     */
    public function duplicate(WorkflowTemplate $template)
    {
        // Charger toutes les étapes avec leurs assignations
        $template->load(['steps.assignments']);

        // Créer une copie du template
        $newTemplate = $template->replicate();
        $newTemplate->name = "Copie de {$template->name}";
        $newTemplate->created_by = Auth::id();
        $newTemplate->save();

        // Dupliquer toutes les étapes avec leurs assignations
        foreach ($template->steps as $step) {
            $newStep = $step->replicate();
            $newStep->workflow_template_id = $newTemplate->id;
            $newStep->save();

            // Dupliquer les assignations
            foreach ($step->assignments as $assignment) {
                $newAssignment = $assignment->replicate();
                $newAssignment->workflow_step_id = $newStep->id;
                $newAssignment->save();
            }
        }

        return redirect()
            ->route('workflows.templates.show', $newTemplate)
            ->with('success', 'Le template de workflow a été dupliqué avec succès.');
    }

    /**
     * Récupérer les étapes d'un template
     * API endpoint: GET /api/workflows/templates/{template}/steps
     * Note: Méthode simplifiée pour retourner les étapes structurées au lieu du JSON
     */
    public function getConfiguration(WorkflowTemplate $template)
    {
        $template->load(['steps' => function($query) {
            $query->orderBy('order_index');
        }, 'steps.assignments']);

        return response()->json([
            'success' => true,
            'data' => [
                'template_id' => $template->id,
                'template_name' => $template->name,
                'steps' => $template->steps,
            ]
        ]);
    }

    /**
     * Mettre à jour la configuration complète d'un template
     * API endpoint: PUT /api/workflows/templates/{template}/configuration
     * Note: Méthode simplifiée pour utiliser les étapes de workflow structurées au lieu de JSON
     */
    public function updateConfiguration(Request $request, WorkflowTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'steps' => 'required|array',
            'steps.*.name' => 'required|string|max:100',
            'steps.*.organisation_id' => 'nullable|integer|exists:organisations,id',
            'steps.*.action_id' => 'required|integer|exists:workflow_actions,id',
            'steps.*.order_index' => 'required|integer|min:1',
            'steps.*.auto_assign' => 'nullable|boolean',
            'steps.*.timeout_hours' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Données de configuration invalides', $validator->errors());
        }

        // Vérifier l'unicité des ordres
        $orders = collect($request->steps)->pluck('order_index');
        if ($orders->count() !== $orders->unique()->count()) {
            return $this->errorResponse('Les ordres des étapes doivent être uniques');
        }

        // Suppression des anciennes étapes
        $template->steps()->delete();

        // Création des nouvelles étapes
        foreach ($request->steps as $stepData) {
            $step = new WorkflowStep([
                'name' => $stepData['name'],
                'organisation_id' => $stepData['organisation_id'] ?? null,
                'action_id' => $stepData['action_id'],
                'order_index' => $stepData['order_index'],
                'auto_assign' => $stepData['auto_assign'] ?? false,
                'timeout_hours' => $stepData['timeout_hours'] ?? null,
            ]);

            $template->steps()->save($step);
        }

        return $this->successResponse('Configuration mise à jour avec succès', [
            'template_id' => $template->id,
            'steps_count' => $template->steps()->count(),
        ]);
    }

    /**
     * Ajouter une étape à un template de workflow
     * API endpoint: POST /api/workflows/templates/{template}/steps
     * Note: Méthode simplifiée pour utiliser les étapes de workflow structurées au lieu de JSON
     */
    public function addConfigurationStep(Request $request, WorkflowTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'organisation_id' => 'nullable|integer|exists:organisations,id',
            'action_id' => 'required|integer|exists:workflow_actions,id',
            'order_index' => 'required|integer|min:1',
            'auto_assign' => 'nullable|boolean',
            'timeout_hours' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Données de l\'étape invalides', $validator->errors());
        }

        // Vérifier l'unicité de l'ordre
        if ($template->steps()->where('order_index', $request->order_index)->exists()) {
            return $this->errorResponse('L\'ordre de l\'étape doit être unique');
        }

        // Créer la nouvelle étape
        $step = new WorkflowStep([
            'name' => $request->name,
            'organisation_id' => $request->organisation_id,
            'action_id' => $request->action_id,
            'order_index' => $request->order_index,
            'auto_assign' => $request->auto_assign ?? false,
            'timeout_hours' => $request->timeout_hours,
        ]);

        $template->steps()->save($step);

        return $this->successResponse('Étape ajoutée avec succès', [
            'template_id' => $template->id,
            'step_id' => $step->id,
        ]);
    }

    /**
     * Mettre à jour une étape spécifique dans un template de workflow
     * API endpoint: PUT /api/workflows/templates/{template}/steps/{stepId}
     * Note: Méthode simplifiée pour utiliser les étapes de workflow structurées au lieu de JSON
     */
    public function updateConfigurationStep(Request $request, WorkflowTemplate $template, int $stepId)
    {
        $step = $template->steps()->find($stepId);

        if (!$step) {
            return $this->errorResponse('Étape non trouvée', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'organisation_id' => 'nullable|integer|exists:organisations,id',
            'action_id' => 'sometimes|required|integer|exists:workflow_actions,id',
            'order_index' => 'sometimes|required|integer|min:1',
            'auto_assign' => 'nullable|boolean',
            'timeout_hours' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Données de l\'étape invalides', $validator->errors());
        }

        // Vérifier l'unicité de l'ordre (exclure l'étape actuelle)
        if ($request->has('order_index') && $request->order_index != $step->order_index &&
            $template->steps()->where('order_index', $request->order_index)->exists()) {
            return $this->errorResponse('L\'ordre de l\'étape doit être unique');
        }

        // Mettre à jour les champs de l'étape
        $step->fill($request->only([
            'name', 'organisation_id', 'action_id', 'order_index',
            'auto_assign', 'timeout_hours'
        ]));

        $step->save();

        return $this->successResponse('Étape mise à jour avec succès', [
            'template_id' => $template->id,
            'step' => $step,
        ]);
    }

    /**
     * Supprimer une étape d'un template de workflow
     * API endpoint: DELETE /api/workflows/templates/{template}/steps/{stepId}
     * Note: Méthode simplifiée pour utiliser les étapes de workflow structurées au lieu de JSON
     */
    public function deleteConfigurationStep(WorkflowTemplate $template, int $stepId)
    {
        $step = $template->steps()->find($stepId);

        if (!$step) {
            return $this->errorResponse('Étape non trouvée', null, 404);
        }

        // Supprimer l'étape
        $step->delete();

        // Réordonner les étapes restantes pour éviter les trous dans la séquence
        $template->steps()
            ->where('order_index', '>', $step->order_index)
            ->decrement('order_index');

        return $this->successResponse('Étape supprimée avec succès', [
            'template_id' => $template->id,
            'steps_count' => $template->steps()->count(),
        ]);
    }

    /**
     * Réorganiser les étapes dans un template de workflow
     * API endpoint: PUT /api/workflows/templates/{template}/steps/reorder
     * Note: Méthode simplifiée pour utiliser les étapes de workflow structurées au lieu de JSON
     */
    public function reorderConfigurationSteps(Request $request, WorkflowTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'step_orders' => 'required|array',
            'step_orders.*.id' => 'required|integer|exists:workflow_steps,id',
            'step_orders.*.order_index' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Données de réorganisation invalides', $validator->errors());
        }

        // Vérifier que toutes les étapes appartiennent à ce template
        $stepIds = collect($request->step_orders)->pluck('id')->toArray();
        $templateStepsCount = $template->steps()->whereIn('id', $stepIds)->count();

        if (count($stepIds) !== $templateStepsCount) {
            return $this->errorResponse('Certaines étapes ne font pas partie de ce template');
        }

        // Vérifier l'unicité des ordres
        $orderIndexes = collect($request->step_orders)->pluck('order_index');
        if ($orderIndexes->count() !== $orderIndexes->unique()->count()) {
            return $this->errorResponse('Les ordres des étapes doivent être uniques');
        }

        // Mettre à jour les ordres
        foreach ($request->step_orders as $stepOrder) {
            WorkflowStep::where('id', $stepOrder['id'])
                ->update(['order_index' => $stepOrder['order_index']]);
        }

        return $this->successResponse('Étapes réorganisées avec succès', [
            'template_id' => $template->id,
        ]);
    }



    /**
     * Valider les étapes d'un template
     * API endpoint: POST /api/workflows/templates/{template}/steps/validate
     * Note: Méthode simplifiée pour valider les étapes structurées au lieu de JSON
     */
    public function validateConfiguration(WorkflowTemplate $template)
    {
        $steps = $template->steps;
        $errors = [];
        $warnings = [];

        // Vérifier l'unicité des ordres
        $orderIndexes = $steps->pluck('order_index');
        if ($orderIndexes->count() !== $orderIndexes->unique()->count()) {
            $errors[] = 'Des ordres d\'étapes sont dupliqués';
        }

        // Vérifier la continuité des ordres
        $sortedOrders = $orderIndexes->sort()->values();
        for ($i = 0; $i < $sortedOrders->count(); $i++) {
            if ($sortedOrders[$i] !== $i + 1) {
                $warnings[] = 'Les ordres des étapes ne sont pas continus';
                break;
            }
        }

        $isValid = empty($errors);

        return response()->json([
            'success' => true,
            'data' => [
                'template_id' => $template->id,
                'is_valid' => $isValid,
                'errors' => $errors,
                'warnings' => $warnings,
                'steps_count' => $steps->count(),
            ]
        ]);
    }

    /**
     * Ajouter des méthodes spécifiques pour la gestion de la configuration dans les formulaires web
     */

    /**
     * Récupérer les étapes d'un template pour les formulaires
     */
    public function getConfigurationForForm(WorkflowTemplate $template)
    {
        $template->load(['steps' => function($query) {
            $query->orderBy('order_index');
        }]);

        return response()->json([
            'template' => $template,
            'steps' => $template->steps->map(function($step) {
                return [
                    'id' => $step->id,
                    'name' => $step->name,
                    'order_index' => $step->order_index,
                    'action_id' => $step->action_id,
                    'organisation_id' => $step->organisation_id,
                    'auto_assign' => $step->auto_assign,
                    'timeout_hours' => $step->timeout_hours,
                ];
            }),
        ]);
    }

    /**
     * Vérifier la cohérence des étapes d'un template
     * Note: Méthode simplifiée puisqu'il n'y a plus de synchronisation entre JSON et DB
     */
    public function syncConfigurationWithSteps(WorkflowTemplate $template)
    {
        $steps = $template->steps()->orderBy('order_index')->get();

        // Vérifier l'intégrité des ordres
        $orderIndexes = $steps->pluck('order_index');
        $hasDuplicates = $orderIndexes->count() !== $orderIndexes->unique()->count();

        // Vérifier la continuité des ordres
        $hasGaps = false;
        $sortedOrders = $orderIndexes->sort()->values();
        for ($i = 0; $i < $sortedOrders->count(); $i++) {
            if ($sortedOrders[$i] !== $i + 1) {
                $hasGaps = true;
                break;
            }
        }

        return response()->json([
            'template_id' => $template->id,
            'steps_count' => $steps->count(),
            'has_order_issues' => $hasDuplicates || $hasGaps,
            'has_duplicate_orders' => $hasDuplicates,
            'has_order_gaps' => $hasGaps
        ]);
    }
}
