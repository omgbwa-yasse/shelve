<?php

namespace App\Http\Controllers;

use App\Models\WorkflowInstance;
use App\Models\WorkflowDefinition;
use Illuminate\Http\Request;

class WorkflowInstanceController extends Controller
{
    public function index()
    {
        $instances = WorkflowInstance::with(['definition', 'starter'])
            ->orderBy('started_at', 'desc')
            ->paginate(20);

        return view('workflows.instances.index', compact('instances'));
    }

    public function create()
    {
        $definitions = WorkflowDefinition::active()->get();
        return view('workflows.instances.create', compact('definitions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'definition_id' => 'required|exists:workflow_definitions,id',
            'name' => 'required|string|max:190',
        ]);

        $instance = WorkflowInstance::create([
            ...$validated,
            'status' => 'running',
            'current_state' => [],
            'started_by' => auth()->id(),
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
}
