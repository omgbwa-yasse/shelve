<?php

namespace App\Http\Controllers;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Services\WorkflowEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowInstanceController extends Controller
{
    protected WorkflowEngine $workflowEngine;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    public function index()
    {
        $query = WorkflowInstance::with(['definition', 'starter', 'updater', 'completer'])
            ->orderBy('started_at', 'desc');

        if (! Auth::user()->isSuperAdmin()) {
            $query->byOrganisation(Auth::user()->current_organisation_id);
        }

        $instances = $query->paginate(20);

        return view('workflows.instances.index', compact('instances'));
    }

    public function create(Request $request)
    {
        $query = WorkflowDefinition::active();

        if (! Auth::user()->isSuperAdmin()) {
            $query->byOrganisation(Auth::user()->current_organisation_id);
        }

        $definitions = $query->get()->filter(fn ($definition) => $definition->canStart());

        return view('workflows.instances.create', [
            'definitions' => $definitions,
            'record' => $request->has('record_id') ? \App\Models\Record::find($request->input('record_id')) : null,
        ]);
    }

    public function store(Request $request)
    {
        if (! Auth::check()) {
            abort(401, 'Authentication required');
        }

        $validated = $request->validate([
            'definition_id' => 'required|exists:workflow_definitions,id',
            'name' => 'required|string|max:190',
            'record_id' => 'nullable|exists:records,id',
        ]);

        $definition = WorkflowDefinition::findOrFail($validated['definition_id']);

        // Sécurité de démarrage (étape 10) : public/privé par utilisateurs/rôles.
        if (! $definition->canStart()) {
            abort(403, 'Vous n\'êtes pas autorisé à démarrer ce workflow.');
        }

        // Titre auto-rempli depuis la notice (si fournie).
        $name = $validated['name'];

        if (isset($validated['record_id'])) {
            $record = \App\Models\Record::find($validated['record_id']);
            $name = ($record ? $record->name.' — ' : '').$definition->name;
        }

        $instance = WorkflowInstance::create([
            'definition_id' => $definition->id,
            'name' => $name,
            'status' => 'running',
            'current_state' => ['record_id' => $validated['record_id'] ?? null],
            'organisation_id' => Auth::user()->current_organisation_id,
            'started_by' => Auth::id(),
        ]);

        return redirect()->route('workflows.instances.show', $instance)
            ->with('success', 'Workflow instance started successfully.');
    }

    /**
     * Démarrage d'un workflow depuis la fiche d'une notice (titre auto-rempli).
     */
    public function startFromRecord(Request $request, \App\Models\Record $record)
    {
        $query = WorkflowDefinition::active();

        if (! Auth::user()->isSuperAdmin()) {
            $query->byOrganisation(Auth::user()->current_organisation_id);
        }

        $definitions = $query->get()->filter(fn ($definition) => $definition->canStart());

        return view('workflows.instances.create', [
            'definitions' => $definitions,
            'record' => $record,
        ]);
    }

    public function show(WorkflowInstance $instance)
    {
        $this->authorize('view', $instance);
        $instance->load(['definition', 'tasks', 'starter']);

        return view('workflows.instances.show', compact('instance'));
    }

    public function destroy(WorkflowInstance $instance)
    {
        $this->authorize('delete', $instance);
        $instance->delete();

        return redirect()->route('workflows.instances.index')
            ->with('success', 'Workflow instance deleted successfully.');
    }

    /**
     * Start a workflow instance
     */
    public function start(WorkflowInstance $instance)
    {
        $this->authorize('update', $instance);

        try {
            $this->workflowEngine->startWorkflow($instance);

            return redirect()->route('workflows.instances.show', $instance)
                ->with('success', 'Workflow started successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to start workflow: '.$e->getMessage());
        }
    }

    /**
     * Pause a workflow instance
     */
    public function pause(WorkflowInstance $instance)
    {
        $this->authorize('update', $instance);

        try {
            $this->workflowEngine->pauseWorkflow($instance);

            return redirect()->route('workflows.instances.show', $instance)
                ->with('success', 'Workflow paused successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to pause workflow: '.$e->getMessage());
        }
    }

    /**
     * Resume a workflow instance
     */
    public function resume(WorkflowInstance $instance)
    {
        $this->authorize('update', $instance);

        try {
            $this->workflowEngine->resumeWorkflow($instance);

            return redirect()->route('workflows.instances.show', $instance)
                ->with('success', 'Workflow resumed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to resume workflow: '.$e->getMessage());
        }
    }

    /**
     * Cancel a workflow instance
     */
    public function cancel(WorkflowInstance $instance)
    {
        $this->authorize('update', $instance);

        try {
            $this->workflowEngine->cancelWorkflow($instance);

            return redirect()->route('workflows.instances.show', $instance)
                ->with('success', 'Workflow cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to cancel workflow: '.$e->getMessage());
        }
    }
}
