<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstance;
use App\Models\WorkflowDefinition;
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
        $instances = WorkflowInstance::with(['definition', 'starter', 'updater', 'completer'])
            ->orderBy('started_at', 'desc')
            ->paginate(20);

        return view('workflows.instances.index', compact('instances'));
    }

    public function create()
    {
        return view('workflows.instances.create', ['definitions' => WorkflowDefinition::active()->get()]);
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $validated = $request->validate([
            'definition_id' => 'required|exists:workflow_definitions,id',
            'name' => 'required|string|max:190',
        ]);

        $instance = WorkflowInstance::create([
            ...$validated,
            'status' => 'running',
            'current_state' => [],
            'started_by' => Auth::id(),
        ]);

        return redirect()->route('workflows.instances.show', $instance)
            ->with('success', 'Workflow instance started successfully.');
    }

    public function show(WorkflowInstance $instance)
    {
        $instance->load(['definition', 'tasks', 'starter']);
        return view('workflows.instances.show', compact('instance'));
    }

    public function destroy(WorkflowInstance $instance)
    {
        $instance->delete();

        return redirect()->route('workflows.instances.index')
            ->with('success', 'Workflow instance deleted successfully.');
    }

    /**
     * Start a workflow instance
     */
    public function start(WorkflowInstance $instance)
    {
        try {
            $this->workflowEngine->startWorkflow($instance);

            return redirect()->route('workflows.instances.show', $instance)
                ->with('success', 'Workflow started successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to start workflow: ' . $e->getMessage());
        }
    }

    /**
     * Pause a workflow instance
     */
    public function pause(WorkflowInstance $instance)
    {
        try {
            $this->workflowEngine->pauseWorkflow($instance);

            return redirect()->route('workflows.instances.show', $instance)
                ->with('success', 'Workflow paused successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to pause workflow: ' . $e->getMessage());
        }
    }

    /**
     * Resume a workflow instance
     */
    public function resume(WorkflowInstance $instance)
    {
        try {
            $this->workflowEngine->resumeWorkflow($instance);

            return redirect()->route('workflows.instances.show', $instance)
                ->with('success', 'Workflow resumed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to resume workflow: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a workflow instance
     */
    public function cancel(WorkflowInstance $instance)
    {
        try {
            $this->workflowEngine->cancelWorkflow($instance);

            return redirect()->route('workflows.instances.show', $instance)
                ->with('success', 'Workflow cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to cancel workflow: ' . $e->getMessage());
        }
    }
}
