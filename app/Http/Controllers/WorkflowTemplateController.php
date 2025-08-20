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
     * Valider l'unicité des IDs et ordres dans une configuration
     */
    private function validateConfigurationUniqueness(array $configuration): ?array
    {
        $config = collect($configuration);

        // Vérifier l'unicité des IDs
        $ids = $config->pluck('id');
        if ($ids->count() !== $ids->unique()->count()) {
            return ['Les IDs des étapes doivent être uniques'];
        }

        // Vérifier l'unicité des ordres
        $ordres = $config->pluck('ordre');
        if ($ordres->count() !== $ordres->unique()->count()) {
            return ['Les ordres des étapes doivent être uniques'];
        }

        return null;
    }

    /**
     * Effectuer les validations métier pour une configuration
     */
    private function performConfigurationValidation(array $configuration): array
    {
        $errors = [];
        $warnings = [];

        if (empty($configuration)) {
            $errors[] = 'La configuration est vide';
            return [$errors, $warnings];
        }

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
            'category' => [
                'required',
                'string',
                Rule::in(array_keys(WorkflowCategory::forSelect()))
            ],
            'is_active' => 'boolean',
            'configuration' => self::NULLABLE_ARRAY,
            'configuration.*.id' => 'required_with:configuration|string',
            'configuration.*.name' => 'required_with:configuration|string',
            'configuration.*.organisation_id' => self::NULLABLE_ORG_EXISTS,
            'configuration.*.action_id' => 'required_with:configuration|integer|exists:workflow_actions,id',
            'configuration.*.ordre' => 'required_with:configuration|integer|min:1',
            'configuration.*.conditions' => self::NULLABLE_ARRAY,
            'configuration.*.auto_assign' => self::NULLABLE_BOOLEAN,
            'configuration.*.timeout_hours' => self::NULLABLE_INTEGER_MIN_1,
            'configuration.*.metadata' => self::NULLABLE_ARRAY,
        ]);

        // Valider l'unicité des IDs et ordres si configuration fournie
        if (!empty($validated['configuration'])) {
            $config = collect($validated['configuration']);

            // Vérifier l'unicité des IDs
            $ids = $config->pluck('id');
            if ($ids->count() !== $ids->unique()->count()) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['configuration' => 'Les IDs des étapes doivent être uniques.']);
            }

            // Vérifier l'unicité des ordres
            $ordres = $config->pluck('ordre');
            if ($ordres->count() !== $ordres->unique()->count()) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['configuration' => 'Les ordres des étapes doivent être uniques.']);
            }
        }

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
            'category' => [
                'required',
                'string',
                Rule::in(array_keys(WorkflowCategory::forSelect()))
            ],
            'is_active' => 'boolean',
            'configuration' => self::NULLABLE_ARRAY,
            'configuration.*.id' => self::REQUIRED_STRING,
            'configuration.*.name' => self::REQUIRED_STRING,
            'configuration.*.organisation_id' => self::NULLABLE_ORG_EXISTS,
            'configuration.*.action_id' => self::REQUIRED_ACTION_EXISTS,
            'configuration.*.ordre' => self::REQUIRED_INTEGER_MIN_1,
            'configuration.*.conditions' => self::NULLABLE_ARRAY,
            'configuration.*.auto_assign' => self::NULLABLE_BOOLEAN,
            'configuration.*.timeout_hours' => self::NULLABLE_INTEGER_MIN_1,
            'configuration.*.metadata' => self::NULLABLE_ARRAY,
        ]);

        // Valider l'unicité des IDs et ordres si configuration fournie
        if (!empty($validated['configuration'])) {
            $config = collect($validated['configuration']);

            // Vérifier l'unicité des IDs
            $ids = $config->pluck('id');
            if ($ids->count() !== $ids->unique()->count()) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['configuration' => 'Les IDs des étapes doivent être uniques.']);
            }

            // Vérifier l'unicité des ordres
            $ordres = $config->pluck('ordre');
            if ($ordres->count() !== $ordres->unique()->count()) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['configuration' => 'Les ordres des étapes doivent être uniques.']);
            }
        }

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
     * Récupérer la configuration JSON complète d'un template
     * API endpoint: GET /api/workflows/templates/{template}/configuration
     */
    public function getConfiguration(WorkflowTemplate $template)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'template_id' => $template->id,
                'template_name' => $template->name,
                'configuration' => $template->configuration ?? [],
            ]
        ]);
    }

    /**
     * Mettre à jour la configuration JSON complète d'un template
     * API endpoint: PUT /api/workflows/templates/{template}/configuration
     */
    public function updateConfiguration(Request $request, WorkflowTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'configuration' => 'required|array',
            'configuration.*.id' => self::REQUIRED_STRING,
            'configuration.*.name' => self::REQUIRED_STRING,
            'configuration.*.organisation_id' => self::NULLABLE_ORG_EXISTS,
            'configuration.*.action_id' => self::REQUIRED_ACTION_EXISTS,
            'configuration.*.ordre' => self::REQUIRED_INTEGER_MIN_1,
            'configuration.*.conditions' => self::NULLABLE_ARRAY,
            'configuration.*.auto_assign' => self::NULLABLE_BOOLEAN,
            'configuration.*.timeout_hours' => self::NULLABLE_INTEGER_MIN_1,
            'configuration.*.metadata' => self::NULLABLE_ARRAY,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Données de configuration invalides', $validator->errors());
        }

        // Valider l'unicité des IDs et ordres
        $uniquenessErrors = $this->validateConfigurationUniqueness($request->configuration);
        if ($uniquenessErrors) {
            return $this->errorResponse($uniquenessErrors[0]);
        }

        $template->configuration = $request->configuration;
        $template->save();

        return $this->successResponse('Configuration mise à jour avec succès', [
            'template_id' => $template->id,
            'configuration' => $template->configuration,
        ]);
    }

    /**
     * Ajouter une étape à la configuration JSON
     * API endpoint: POST /api/workflows/templates/{template}/configuration/steps
     */
    public function addConfigurationStep(Request $request, WorkflowTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'id' => self::REQUIRED_STRING,
            'name' => self::REQUIRED_STRING,
            'organisation_id' => self::NULLABLE_ORG_EXISTS,
            'action_id' => self::REQUIRED_ACTION_EXISTS,
            'ordre' => self::REQUIRED_INTEGER_MIN_1,
            'conditions' => self::NULLABLE_ARRAY,
            'auto_assign' => self::NULLABLE_BOOLEAN,
            'timeout_hours' => self::NULLABLE_INTEGER_MIN_1,
            'metadata' => self::NULLABLE_ARRAY,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Données de l\'étape invalides', $validator->errors());
        }

        $configuration = $template->configuration ?? [];

        // Vérifier l'unicité de l'ID et de l'ordre
        $existingIds = collect($configuration)->pluck('id');
        $existingOrdres = collect($configuration)->pluck('ordre');

        if ($existingIds->contains($request->id)) {
            return $this->errorResponse('L\'ID de l\'étape doit être unique');
        }

        if ($existingOrdres->contains($request->ordre)) {
            return $this->errorResponse('L\'ordre de l\'étape doit être unique');
        }

        // Ajouter la nouvelle étape
        $newStep = $request->only([
            'id', 'name', 'organisation_id', 'action_id', 'ordre',
            'conditions', 'auto_assign', 'timeout_hours', 'metadata'
        ]);

        $configuration[] = $newStep;
        $template->configuration = $configuration;
        $template->save();

        return $this->successResponse('Étape ajoutée avec succès', [
            'template_id' => $template->id,
            'step' => $newStep,
            'configuration' => $template->configuration,
        ]);
    }

    /**
     * Mettre à jour une étape spécifique dans la configuration JSON
     * API endpoint: PUT /api/workflows/templates/{template}/configuration/steps/{stepId}
     */
    public function updateConfigurationStep(Request $request, WorkflowTemplate $template, string $stepId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string',
            'organisation_id' => self::NULLABLE_ORG_EXISTS,
            'action_id' => 'sometimes|required|integer|exists:workflow_actions,id',
            'ordre' => 'sometimes|required|integer|min:1',
            'conditions' => self::NULLABLE_ARRAY,
            'auto_assign' => self::NULLABLE_BOOLEAN,
            'timeout_hours' => self::NULLABLE_INTEGER_MIN_1,
            'metadata' => self::NULLABLE_ARRAY,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de l\'étape invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $configuration = $template->configuration ?? [];
        $stepIndex = collect($configuration)->search(function ($step) use ($stepId) {
            return $step['id'] === $stepId;
        });

        if ($stepIndex === false) {
            return response()->json([
                'success' => false,
                'message' => 'Étape non trouvée',
            ], 404);
        }

        // Vérifier l'unicité de l'ordre (exclure l'étape actuelle)
        if ($request->has('ordre')) {
            $configurationWithoutCurrent = $configuration;
            unset($configurationWithoutCurrent[$stepIndex]);
            $existingOrdres = collect($configurationWithoutCurrent)->pluck('ordre');

            if ($existingOrdres->contains($request->ordre)) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'ordre de l\'étape doit être unique',
                ], 422);
            }
        }

        // Mettre à jour l'étape
        $configuration[$stepIndex] = array_merge(
            $configuration[$stepIndex],
            $request->only([
                'name', 'organisation_id', 'action_id', 'ordre',
                'conditions', 'auto_assign', 'timeout_hours', 'metadata'
            ])
        );

        $template->configuration = $configuration;
        $template->save();

        return response()->json([
            'success' => true,
            'message' => 'Étape mise à jour avec succès',
            'data' => [
                'template_id' => $template->id,
                'step' => $configuration[$stepIndex],
                'configuration' => $template->configuration,
            ]
        ]);
    }

    /**
     * Supprimer une étape de la configuration JSON
     * API endpoint: DELETE /api/workflows/templates/{template}/configuration/steps/{stepId}
     */
    public function deleteConfigurationStep(WorkflowTemplate $template, string $stepId)
    {
        $configuration = $template->configuration ?? [];
        $stepIndex = collect($configuration)->search(function ($step) use ($stepId) {
            return $step['id'] === $stepId;
        });

        if ($stepIndex === false) {
            return response()->json([
                'success' => false,
                'message' => 'Étape non trouvée',
            ], 404);
        }

        // Supprimer l'étape
        unset($configuration[$stepIndex]);
        $configuration = array_values($configuration); // Réindexer le tableau

        $template->configuration = $configuration;
        $template->save();

        return response()->json([
            'success' => true,
            'message' => 'Étape supprimée avec succès',
            'data' => [
                'template_id' => $template->id,
                'configuration' => $template->configuration,
            ]
        ]);
    }

    /**
     * Réorganiser les étapes dans la configuration JSON
     * API endpoint: PUT /api/workflows/templates/{template}/configuration/reorder
     */
    public function reorderConfigurationSteps(Request $request, WorkflowTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'step_orders' => 'required|array',
            'step_orders.*.id' => 'required|string',
            'step_orders.*.ordre' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données de réorganisation invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $configuration = $template->configuration ?? [];
        $stepOrders = collect($request->step_orders);

        // Vérifier que tous les IDs existent
        $configIds = collect($configuration)->pluck('id');
        $requestIds = $stepOrders->pluck('id');

        if (!$requestIds->diff($configIds)->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Certains IDs d\'étapes n\'existent pas',
            ], 422);
        }

        // Vérifier l'unicité des ordres
        $ordres = $stepOrders->pluck('ordre');
        if ($ordres->count() !== $ordres->unique()->count()) {
            return response()->json([
                'success' => false,
                'message' => 'Les ordres des étapes doivent être uniques',
            ], 422);
        }

        // Mettre à jour les ordres
        foreach ($configuration as &$step) {
            $newOrder = $stepOrders->firstWhere('id', $step['id']);
            if ($newOrder) {
                $step['ordre'] = $newOrder['ordre'];
            }
        }

        // Trier par ordre
        usort($configuration, function ($a, $b) {
            return $a['ordre'] <=> $b['ordre'];
        });

        $template->configuration = $configuration;
        $template->save();

        return response()->json([
            'success' => true,
            'message' => 'Étapes réorganisées avec succès',
            'data' => [
                'template_id' => $template->id,
                'configuration' => $template->configuration,
            ]
        ]);
    }

    /**
     * Valider la configuration JSON d'un template
     * API endpoint: POST /api/workflows/templates/{template}/configuration/validate
     */
    public function validateConfiguration(WorkflowTemplate $template)
    {
        $configuration = $template->configuration ?? [];
        [$errors, $warnings] = $this->performConfigurationValidation($configuration);
        $isValid = empty($errors);

        return response()->json([
            'success' => true,
            'data' => [
                'template_id' => $template->id,
                'is_valid' => $isValid,
                'errors' => $errors,
                'warnings' => $warnings,
                'steps_count' => count($configuration),
            ]
        ]);
    }

    /**
     * Ajouter des méthodes spécifiques pour la gestion de la configuration dans les formulaires web
     */

    /**
     * Récupérer la configuration JSON d'un template pour les formulaires
     */
    public function getConfigurationForForm(WorkflowTemplate $template)
    {
        $template->load(['steps' => function($query) {
            $query->orderBy('order_index');
        }]);

        return response()->json([
            'template' => $template,
            'configuration' => $template->configuration ?? [],
            'existing_steps' => $template->steps->map(function($step) {
                return [
                    'id' => $step->id,
                    'name' => $step->name,
                    'order_index' => $step->order_index,
                ];
            }),
        ]);
    }

    /**
     * Synchroniser la configuration JSON avec les étapes de la base de données
     */
    public function syncConfigurationWithSteps(WorkflowTemplate $template)
    {
        $configuration = $template->configuration ?? [];
        $steps = $template->steps()->orderBy('order_index')->get();

        // Créer une correspondance entre la configuration JSON et les étapes DB
        $syncData = [];
        foreach ($configuration as $configStep) {
            $dbStep = $steps->firstWhere('name', $configStep['name']) ??
                     $steps->firstWhere('order_index', $configStep['ordre']);

            $syncData[] = [
                'config_step' => $configStep,
                'db_step' => $dbStep,
                'needs_sync' => $dbStep ? false : true,
            ];
        }

        return response()->json([
            'template_id' => $template->id,
            'sync_data' => $syncData,
            'needs_attention' => collect($syncData)->contains('needs_sync', true),
        ]);
    }
}
