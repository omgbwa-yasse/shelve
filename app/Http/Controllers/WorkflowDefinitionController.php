<?php

namespace App\Http\Controllers;

use App\Models\WorkflowDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowDefinitionController extends Controller
{
    public function index()
    {
        $definitions = WorkflowDefinition::with(['creator', 'updater', 'instances'])
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
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'bpmn_xml' => 'required|string',
            'status' => 'required|string|in:draft,active,archived',
        ]);

        // Calculate version
        $latestVersion = WorkflowDefinition::where('name', $validated['name'])->max('version') ?? 0;

        $definition = WorkflowDefinition::create([
            ...$validated,
            'version' => $latestVersion + 1,
            'created_by' => Auth::id(),
        ]);

        // Rediriger vers la configuration BPMN
        return redirect()->route('workflows.definitions.configuration.create', $definition)
            ->with('success', 'Workflow créé. Configurez maintenant le diagramme BPMN.');
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
            'status' => 'required|string|in:draft,active,archived',
        ]);

        $definition->update([
            ...$validated,
            'updated_by' => Auth::id(),
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

    /**
     * Show the form for creating BPMN configuration
     */
    public function createConfiguration(WorkflowDefinition $definition)
    {
        return view('workflows.definitions.configuration', compact('definition'));
    }

    /**
     * Store BPMN configuration for a workflow definition
     */
    public function storeConfiguration(Request $request, WorkflowDefinition $definition)
    {
        $validated = $request->validate([
            'bpmn_xml' => 'required|string',
        ]);

        $definition->update([
            'bpmn_xml' => $validated['bpmn_xml'],
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('workflows.definitions.show', $definition)
            ->with('success', 'Configuration BPMN enregistrée avec succès.');
    }

    /**
     * Show the form for editing BPMN configuration
     */
    public function editConfiguration(WorkflowDefinition $definition)
    {
        $isEdit = true;
        return view('workflows.definitions.configuration', compact('definition', 'isEdit'));
    }

    /**
     * Update BPMN configuration for a workflow definition
     */
    public function updateConfiguration(Request $request, WorkflowDefinition $definition)
    {
        $validated = $request->validate([
            'bpmn_xml' => 'required|string',
        ]);

        $definition->update([
            'bpmn_xml' => $validated['bpmn_xml'],
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('workflows.definitions.show', $definition)
            ->with('success', 'Configuration BPMN mise à jour avec succès.');
    }
}
