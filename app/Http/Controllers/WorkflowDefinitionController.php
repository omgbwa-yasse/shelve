<?php

namespace App\Http\Controllers;

use App\Models\WorkflowDefinition;
use Illuminate\Http\Request;

class WorkflowDefinitionController extends Controller
{
    public function index()
    {
        $definitions = WorkflowDefinition::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('workflows.definitions.index', compact('definitions'));
    }

    public function create()
    {
        return view('workflows.definitions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'bpmn_xml' => 'required|string',
            'status' => 'required|in:draft,active,archived',
        ]);

        $definition = WorkflowDefinition::create([
            ...$validated,
            'version' => 1,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('workflows.definitions.show', $definition)
            ->with('success', 'Workflow definition created successfully.');
    }

    public function show(WorkflowDefinition $definition)
    {
        $definition->load(['instances', 'transitions', 'creator']);
        return view('workflows.definitions.show', compact('definition'));
    }

    public function edit(WorkflowDefinition $definition)
    {
        return view('workflows.definitions.edit', compact('definition'));
    }

    public function update(Request $request, WorkflowDefinition $definition)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'bpmn_xml' => 'required|string',
            'status' => 'required|in:draft,active,archived',
        ]);

        $definition->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('workflows.definitions.show', $definition)
            ->with('success', 'Workflow definition updated successfully.');
    }

    public function destroy(WorkflowDefinition $definition)
    {
        $definition->delete();

        return redirect()->route('workflows.definitions.index')
            ->with('success', 'Workflow definition deleted successfully.');
    }
}
