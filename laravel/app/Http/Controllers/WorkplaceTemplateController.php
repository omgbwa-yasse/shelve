<?php

namespace App\Http\Controllers;

use App\Models\WorkplaceTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkplaceTemplateController extends Controller
{
    public function index()
    {
        $templates = WorkplaceTemplate::active()
            ->orderBy('display_order')
            ->get();

        return view('workplaces.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('workplaces.templates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'category' => 'nullable|string',
            'default_structure' => 'nullable|json',
            'default_settings' => 'nullable|json',
        ]);

        WorkplaceTemplate::create([
            ...$validated,
            'code' => 'TPL-' . strtoupper(uniqid()),
            'created_by' => Auth::id(),
            'is_active' => true,
            'is_system' => false,
            'default_structure' => isset($validated['default_structure']) ? json_decode($validated['default_structure'], true) : [],
            'default_settings' => isset($validated['default_settings']) ? json_decode($validated['default_settings'], true) : [],
        ]);

        return redirect()->route('workplaces.templates.index')
            ->with('success', 'Modèle créé avec succès');
    }

    public function show(WorkplaceTemplate $template)
    {
        return view('workplaces.templates.show', compact('template'));
    }

    public function edit(WorkplaceTemplate $template)
    {
        if ($template->is_system) {
            return back()->withErrors(['error' => 'Impossible de modifier un modèle système']);
        }
        return view('workplaces.templates.edit', compact('template'));
    }

    public function update(Request $request, WorkplaceTemplate $template)
    {
        if ($template->is_system) {
            return back()->withErrors(['error' => 'Impossible de modifier un modèle système']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'category' => 'nullable|string',
            'default_structure' => 'nullable|json',
            'default_settings' => 'nullable|json',
        ]);

        $template->update([
            ...$validated,
            'default_structure' => isset($validated['default_structure']) ? json_decode($validated['default_structure'], true) : $template->default_structure,
            'default_settings' => isset($validated['default_settings']) ? json_decode($validated['default_settings'], true) : $template->default_settings,
        ]);

        return redirect()->route('workplaces.templates.index')
            ->with('success', 'Modèle mis à jour avec succès');
    }

    public function destroy(WorkplaceTemplate $template)
    {
        if ($template->is_system) {
            return back()->withErrors(['error' => 'Impossible de supprimer un modèle système']);
        }

        $template->delete();
        return redirect()->route('workplaces.templates.index')
            ->with('success', 'Modèle supprimé avec succès');
    }
}
