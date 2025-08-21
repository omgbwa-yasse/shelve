<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Models\PromptTransaction;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PromptManagementController extends Controller
{
    /**
     * Display a listing of the prompts.
     */
    public function index(Request $request)
    {
        $currentOrganisation = Auth::user()->currentOrganisation;
        $organisationId = $currentOrganisation?->id;

        $query = Prompt::query()
            ->with(['user:id,name', 'organisation:id,name'])
            ->where(function($q) use ($organisationId) {
                $q->where('organisation_id', $organisationId)
                  ->orWhere('is_system', true)
                  ->orWhere(function($q2) {
                      $q2->whereNull('organisation_id')
                         ->where('user_id', Auth::id());
                  });
            });

        // Filtres
        if ($request->has('is_system')) {
            $query->where('is_system', $request->boolean('is_system'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $prompts = $query->orderBy('is_system', 'desc')
                         ->orderBy('title')
                         ->paginate(15)
                         ->withQueryString();

        return view('settings.prompts.index', compact('prompts'));
    }

    /**
     * Show the form for creating a new prompt.
     */
    public function create()
    {
        $currentOrganisation = Auth::user()->currentOrganisation;
        return view('settings.prompts.create', compact('currentOrganisation'));
    }

    /**
     * Store a newly created prompt in storage.
     */
    public function store(Request $request)
    {
        $currentOrganisation = Auth::user()->currentOrganisation;

        $request->validate([
            'title' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('prompts')->where(function ($query) use ($request, $currentOrganisation) {
                    return $query->where('is_system', $request->boolean('is_system'))
                                ->where('organisation_id', $currentOrganisation?->id)
                                ->where('user_id', Auth::id());
                }),
            ],
            'content' => 'required|string',
            'is_system' => 'boolean',
        ]);

        $prompt = new Prompt();
        $prompt->title = $request->title;
        $prompt->content = $request->content;
        $prompt->is_system = $request->boolean('is_system');
        $prompt->organisation_id = $currentOrganisation?->id;
        $prompt->user_id = Auth::id();
        $prompt->save();

        return redirect()
            ->route('settings.prompts.index')
            ->with('success', 'Prompt créé avec succès.');
    }

    /**
     * Display the specified prompt.
     */
    public function show(Prompt $prompt)
    {
        $this->authorizePromptAccess($prompt);

        // Récupérer les transactions associées à ce prompt
        $transactions = PromptTransaction::where('prompt_id', $prompt->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('settings.prompts.show', compact('prompt', 'transactions'));
    }

    /**
     * Show the form for editing the specified prompt.
     */
    public function edit(Prompt $prompt)
    {
        $this->authorizePromptAccess($prompt);

        $currentOrganisation = Auth::user()->currentOrganisation;
        return view('settings.prompts.edit', compact('prompt', 'currentOrganisation'));
    }

    /**
     * Update the specified prompt in storage.
     */
    public function update(Request $request, Prompt $prompt)
    {
        $this->authorizePromptAccess($prompt);

        $currentOrganisation = Auth::user()->currentOrganisation;

        $request->validate([
            'title' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('prompts')->where(function ($query) use ($request, $currentOrganisation, $prompt) {
                    return $query->where('is_system', $request->boolean('is_system'))
                                ->where('organisation_id', $currentOrganisation?->id)
                                ->where('user_id', Auth::id())
                                ->where('id', '!=', $prompt->id);
                }),
            ],
            'content' => 'required|string',
            'is_system' => 'boolean',
        ]);

        $prompt->title = $request->title;
        $prompt->content = $request->content;
        $prompt->is_system = $request->boolean('is_system');
        $prompt->save();

        return redirect()
            ->route('settings.prompts.index')
            ->with('success', 'Prompt mis à jour avec succès.');
    }

    /**
     * Remove the specified prompt from storage.
     */
    public function destroy(Prompt $prompt)
    {
        $this->authorizePromptAccess($prompt);

        // Vérifier s'il y a des transactions associées
        $transactionCount = PromptTransaction::where('prompt_id', $prompt->id)->count();

        if ($transactionCount > 0) {
            return redirect()
                ->route('settings.prompts.index')
                ->with('error', 'Impossible de supprimer ce prompt car il est utilisé dans des transactions.');
        }

        $prompt->delete();

        return redirect()
            ->route('settings.prompts.index')
            ->with('success', 'Prompt supprimé avec succès.');
    }

    /**
     * Vérifier si l'utilisateur a le droit d'accéder au prompt.
     */
    private function authorizePromptAccess(Prompt $prompt)
    {
        $currentOrganisation = Auth::user()->currentOrganisation;
        $organisationId = $currentOrganisation?->id;

        // Autoriser l'accès si le prompt est:
        // 1. Un prompt système
        // 2. Un prompt de l'organisation actuelle
        // 3. Un prompt personnel de l'utilisateur connecté
        $canAccess = $prompt->is_system
                    || $prompt->organisation_id == $organisationId
                    || ($prompt->user_id == Auth::id() && is_null($prompt->organisation_id));

        if (!$canAccess) {
            abort(403, "Vous n'avez pas l'autorisation d'accéder à ce prompt.");
        }
    }
}
