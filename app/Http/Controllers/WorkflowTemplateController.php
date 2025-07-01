<?php

namespace App\Http\Controllers;

use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WorkflowTemplateController extends Controller
{
    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(WorkflowTemplate::class, 'template');
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
        return view('workflow.templates.create');
    }

    /**
     * Enregistrer un nouveau template de workflow
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:workflow_templates',
            'description' => 'nullable|string',
            'category' => 'required|string|max:50',
            'is_active' => 'boolean',
            'configuration' => 'nullable|array',
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
        return view('workflow.templates.edit', compact('template'));
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
            'category' => 'required|string|max:50',
            'is_active' => 'boolean',
            'configuration' => 'nullable|array',
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
}
