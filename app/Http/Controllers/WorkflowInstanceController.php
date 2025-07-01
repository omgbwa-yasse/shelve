<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\WorkflowInstance;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepInstance;
use App\Enums\WorkflowInstanceStatus;
use App\Enums\WorkflowStepInstanceStatus;
use App\Enums\AssignmentType;
use App\Enums\TaskAssigneeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Carbon\Carbon;

class WorkflowInstanceController extends Controller
{
    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(WorkflowInstance::class, 'instance');
    }

    /**
     * Afficher la liste des instances de workflow
     */
    public function index(Request $request)
    {
        $query = WorkflowInstance::with(['template', 'mail', 'currentStep']);

        // Filtrage par status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrage par template
        if ($request->has('template')) {
            $query->where('workflow_template_id', $request->template);
        }

        // Filtrage par courrier
        if ($request->has('mail')) {
            $query->where('mail_id', $request->mail);
        }

        // Filtrage par date d'échéance
        if ($request->has('due_date')) {
            if ($request->due_date === 'overdue') {
                $query->where('due_date', '<', now())
                      ->whereNull('completed_at');
            } elseif ($request->due_date === 'today') {
                $query->whereDate('due_date', now());
            } elseif ($request->due_date === 'week') {
                $query->whereBetween('due_date', [now(), now()->addWeek()]);
            }
        }

        $instances = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $templates = WorkflowTemplate::where('is_active', true)->orderBy('name')->get();

        return view('workflow.instances.index', compact('instances', 'templates'));
    }

    /**
     * Afficher le formulaire de création d'une instance
     */
    public function create(Request $request)
    {
        $mail = null;
        if ($request->has('mail_id')) {
            $mail = Mail::findOrFail($request->mail_id);
        }

        $templates = WorkflowTemplate::where('is_active', true)->orderBy('name')->get();

        return view('workflow.instances.create', compact('templates', 'mail'));
    }

    /**
     * Stocker une nouvelle instance de workflow
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'workflow_template_id' => 'required|exists:workflow_templates,id',
            'mail_id' => 'required|exists:mails,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Récupérer le template et vérifier qu'il a des étapes
        $template = WorkflowTemplate::with('steps')->findOrFail($validated['workflow_template_id']);

        if ($template->steps->isEmpty()) {
            return redirect()->back()->with('error', 'Le template sélectionné n\'a pas d\'étapes définies.');
        }

        // Vérifier si le courrier a déjà un workflow actif
        $mail = Mail::findOrFail($validated['mail_id']);
        $hasActiveWorkflow = $mail->workflows()->whereIn('status', [
            WorkflowInstanceStatus::PENDING->value,
            WorkflowInstanceStatus::IN_PROGRESS->value,
            WorkflowInstanceStatus::ON_HOLD->value
        ])->exists();

        if ($hasActiveWorkflow) {
            return redirect()->back()->with('error', 'Ce courrier a déjà un workflow actif.');
        }

        DB::beginTransaction();

        try {
            // Créer l'instance de workflow
            $workflowInstance = new WorkflowInstance([
                'workflow_template_id' => $template->id,
                'mail_id' => $validated['mail_id'],
                'status' => WorkflowInstanceStatus::PENDING,
                'initiated_by' => Auth::id(),
                'started_at' => now(),
                'due_date' => $validated['due_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Définir la première étape comme étape courante
            $firstStep = $template->steps->sortBy('order_index')->first();
            $workflowInstance->current_step_id = $firstStep->id;
            $workflowInstance->save();

            // Créer les instances d'étapes pour toutes les étapes du template
            foreach ($template->steps as $step) {
                $dueDate = null;
                if ($step->estimated_duration) {
                    $dueDate = Carbon::now()->addMinutes($step->estimated_duration);
                }

                $stepInstance = new WorkflowStepInstance([
                    'workflow_instance_id' => $workflowInstance->id,
                    'workflow_step_id' => $step->id,
                    'status' => $step->order_index === 0 ? WorkflowStepInstanceStatus::PENDING : WorkflowStepInstanceStatus::PENDING,
                    'due_date' => $dueDate,
                ]);

                // Assigner automatiquement selon les règles d'assignation de l'étape
                $assignments = $step->assignments;
                if ($assignments->isNotEmpty()) {
                    $assignment = $assignments->first();

                    if ($assignment->assignee_user_id) {
                        $stepInstance->assigned_to_user_id = $assignment->assignee_user_id;
                        $stepInstance->assignment_type = AssignmentType::USER;
                    }

                    if ($assignment->assignee_organisation_id) {
                        $stepInstance->assigned_to_organisation_id = $assignment->assignee_organisation_id;
                        $stepInstance->assignment_type = $stepInstance->assigned_to_user_id ?
                            AssignmentType::BOTH : AssignmentType::ORGANISATION;
                    }
                }

                $stepInstance->save();
            }

            // Mettre à jour le statut du workflow en cours
            $workflowInstance->status = WorkflowInstanceStatus::IN_PROGRESS;
            $workflowInstance->save();

            // Associer le workflow au courrier
            $mail->workflow_instance_id = $workflowInstance->id;
            $mail->save();

            DB::commit();

            return redirect()
                ->route('workflows.instances.show', $workflowInstance)
                ->with('success', 'Le workflow a été démarré avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la création du workflow : ' . $e->getMessage());
        }
    }

    /**
     * Afficher une instance de workflow
     */
    public function show(WorkflowInstance $instance)
    {
        $instance->load([
            'template',
            'mail',
            'initiator',
            'currentStep',
            'stepInstances' => function($query) {
                $query->with('step', 'assignedUser', 'assignedOrganisation')
                      ->orderBy('workflow_step_id');
            }
        ]);

        return view('workflow.instances.show', compact('instance'));
    }

    /**
     * Afficher le formulaire d'édition d'une instance
     */
    public function edit(WorkflowInstance $instance)
    {
        if ($instance->status === WorkflowInstanceStatus::COMPLETED ||
            $instance->status === WorkflowInstanceStatus::CANCELLED) {
            return redirect()
                ->route('workflows.instances.show', $instance)
                ->with('error', 'Les workflows terminés ou annulés ne peuvent pas être modifiés.');
        }

        $instance->load(['template', 'mail', 'currentStep']);

        return view('workflow.instances.edit', compact('instance'));
    }

    /**
     * Mettre à jour une instance de workflow
     */
    public function update(Request $request, WorkflowInstance $instance)
    {
        if ($instance->status === WorkflowInstanceStatus::COMPLETED ||
            $instance->status === WorkflowInstanceStatus::CANCELLED) {
            return redirect()
                ->route('workflows.instances.show', $instance)
                ->with('error', 'Les workflows terminés ou annulés ne peuvent pas être modifiés.');
        }

        $validated = $request->validate([
            'status' => [new Enum(WorkflowInstanceStatus::class)],
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $statusChanged = $instance->status->value !== $validated['status'];

        $instance->update([
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? $instance->due_date,
            'notes' => $validated['notes'] ?? $instance->notes,
        ]);

        // Si le statut change à complété, mettre à jour les dates et l'étape courante
        if ($statusChanged && $instance->status === WorkflowInstanceStatus::COMPLETED) {
            $instance->completed_at = now();
            $instance->current_step_id = null;
            $instance->save();

            // Marquer toutes les étapes non terminées comme ignorées
            $instance->stepInstances()
                ->whereIn('status', [
                    WorkflowStepInstanceStatus::PENDING->value,
                    WorkflowStepInstanceStatus::IN_PROGRESS->value
                ])
                ->update(['status' => WorkflowStepInstanceStatus::SKIPPED]);
        }

        return redirect()
            ->route('workflows.instances.show', $instance)
            ->with('success', 'Le workflow a été mis à jour avec succès.');
    }

    /**
     * Supprimer une instance de workflow
     */
    public function destroy(WorkflowInstance $instance)
    {
        // Vérifier si on peut supprimer ce workflow
        if ($instance->status === WorkflowInstanceStatus::IN_PROGRESS) {
            return redirect()
                ->route('workflows.instances.show', $instance)
                ->with('error', 'Impossible de supprimer un workflow en cours. Annulez-le d\'abord.');
        }

        // Détacher du courrier si lié
        if ($instance->mail) {
            $mail = $instance->mail;
            $mail->workflow_instance_id = null;
            $mail->save();
        }

        $instance->delete();

        return redirect()
            ->route('workflows.instances.index')
            ->with('success', 'Le workflow a été supprimé avec succès.');
    }

    /**
     * Annuler un workflow
     */
    public function cancel(WorkflowInstance $instance)
    {
        if ($instance->status === WorkflowInstanceStatus::COMPLETED ||
            $instance->status === WorkflowInstanceStatus::CANCELLED) {
            return redirect()
                ->route('workflows.instances.show', $instance)
                ->with('error', 'Ce workflow est déjà terminé ou annulé.');
        }

        $instance->status = WorkflowInstanceStatus::CANCELLED;
        $instance->save();

        // Marquer toutes les étapes non terminées comme ignorées
        $instance->stepInstances()
            ->whereIn('status', [
                WorkflowStepInstanceStatus::PENDING->value,
                WorkflowStepInstanceStatus::IN_PROGRESS->value
            ])
            ->update(['status' => WorkflowStepInstanceStatus::SKIPPED]);

        return redirect()
            ->route('workflows.instances.show', $instance)
            ->with('success', 'Le workflow a été annulé avec succès.');
    }

    /**
     * Mettre en pause un workflow
     */
    public function hold(WorkflowInstance $instance)
    {
        if ($instance->status !== WorkflowInstanceStatus::IN_PROGRESS) {
            return redirect()
                ->route('workflows.instances.show', $instance)
                ->with('error', 'Seuls les workflows en cours peuvent être mis en pause.');
        }

        $instance->status = WorkflowInstanceStatus::ON_HOLD;
        $instance->save();

        return redirect()
            ->route('workflows.instances.show', $instance)
            ->with('success', 'Le workflow a été mis en pause avec succès.');
    }

    /**
     * Reprendre un workflow en pause
     */
    public function resume(WorkflowInstance $instance)
    {
        if ($instance->status !== WorkflowInstanceStatus::ON_HOLD) {
            return redirect()
                ->route('workflows.instances.show', $instance)
                ->with('error', 'Seuls les workflows en pause peuvent être repris.');
        }

        $instance->status = WorkflowInstanceStatus::IN_PROGRESS;
        $instance->save();

        return redirect()
            ->route('workflows.instances.show', $instance)
            ->with('success', 'Le workflow a été repris avec succès.');
    }

    /**
     * Affiche le tableau de bord du module workflow
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $this->authorize('workflow_dashboard');

        // Statistiques globales
        $stats = [
            'active_workflows' => WorkflowInstance::whereNotIn('status', [WorkflowInstanceStatus::COMPLETED, WorkflowInstanceStatus::CANCELLED])->count(),
            'completed_workflows' => WorkflowInstance::where('status', WorkflowInstanceStatus::COMPLETED)->count(),
            'active_tasks' => \App\Models\Task::whereNull('completed_at')->count(),
            'overdue_items' => WorkflowInstance::where('due_date', '<', now())
                ->whereNull('completed_at')
                ->whereNotIn('status', [WorkflowInstanceStatus::COMPLETED, WorkflowInstanceStatus::CANCELLED])
                ->count()
        ];

        // Workflows assignés à l'utilisateur actuel
        $myWorkflows = WorkflowInstance::whereHas('stepInstances', function ($query) {
            $query->where(function($q) {
                $q->where('assigned_to_user_id', Auth::id())
                  ->whereIn('assignment_type', [AssignmentType::USER, AssignmentType::BOTH]);
            });
        })
        ->whereNotIn('status', [WorkflowInstanceStatus::COMPLETED, WorkflowInstanceStatus::CANCELLED])
        ->with(['template', 'currentStep', 'currentStep.step'])
        ->orderBy('due_date', 'asc')
        ->limit(5)
        ->get();

        // Tâches assignées à l'utilisateur actuel
        $myTasks = \App\Models\Task::whereHas('assignments', function ($query) {
            $query->where('assignee_type', TaskAssigneeType::USER->value)
                  ->where('assignee_user_id', Auth::id());
        })
        ->whereNull('completed_at')
        ->with(['category'])
        ->orderBy('due_date', 'asc')
        ->limit(5)
        ->get();

        // Activités récentes (peuvent être des changements d'état de workflow, des commentaires, etc.)
        $recentActivities = \App\Models\SystemNotification::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Workflows par modèle (pour le graphique)
        $workflowsByTemplate = DB::table('workflow_templates')
            ->select([
                'workflow_templates.name',
                DB::raw('COUNT(CASE WHEN workflow_instances.status NOT IN ("' . WorkflowInstanceStatus::COMPLETED->value . '", "' . WorkflowInstanceStatus::CANCELLED->value . '") THEN 1 ELSE NULL END) as active_count'),
                DB::raw('COUNT(CASE WHEN workflow_instances.status = "' . WorkflowInstanceStatus::COMPLETED->value . '" THEN 1 ELSE NULL END) as completed_count')
            ])
            ->leftJoin('workflow_instances', 'workflow_templates.id', '=', 'workflow_instances.workflow_template_id')
            ->groupBy('workflow_templates.id', 'workflow_templates.name')
            ->orderBy('active_count', 'desc')
            ->get();

        return view('workflow.dashboard', compact('stats', 'myWorkflows', 'myTasks', 'recentActivities', 'workflowsByTemplate'));
    }
}
