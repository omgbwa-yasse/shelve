<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Prompt;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PromptController extends Controller
{
   public function index(): View
   {
       $prompts = Prompt::with('user')
           ->when( function ($query) {
               return $query->where('user_id', Auth::id())
                   ->orWhere('is_public', true);
           })
           ->latest()
           ->paginate(10);

       return view('prompts.index', compact('prompts'));
   }


   public function create(): View
   {
       return view('prompts.create');
   }

   public function store(Request $request): RedirectResponse
   {
       $validated = $request->validate([
           'name' => 'required|string|max:255',
           'instruction' => 'required|string',
           'is_public' => 'boolean',
           'is_draft' => 'boolean',
           'is_archived' => 'boolean',
           'is_system' => 'boolean'
       ]);

       $prompt = Prompt::create($validated);

       return redirect()
           ->route('prompts.show', $prompt)
           ->with('success', 'Prompt créé avec succès.');
   }


   public function show(Prompt $prompt): View
   {
       return view('prompts.show', compact('prompt'));
   }



   public function edit(Prompt $prompt): View
   {
       return view('prompts.edit', compact('prompt'));
   }


   public function update(Request $request, Prompt $prompt): RedirectResponse
   {
       $validated = $request->validate([
           'name' => 'required|string|max:255',
           'instruction' => 'required|string',
           'is_public' => 'boolean',
           'is_draft' => 'boolean',
           'is_archived' => 'boolean',
           'is_system' => 'boolean'
       ]);

       $prompt->update($validated);

       return redirect()
           ->route('prompts.show', $prompt)
           ->with('success', 'Prompt mis à jour avec succès.');
   }


   public function destroy(Prompt $prompt): RedirectResponse
   {
       $prompt->delete();
       return redirect()
           ->route('prompts.index')
           ->with('success', 'Prompt supprimé avec succès.');
   }


   // Méthodes additionnelles utiles
   public function archive(Prompt $prompt): RedirectResponse
   {
       $prompt->update(['is_archived' => true]);
       return back()->with('success', 'Prompt archivé avec succès.');
   }

   public function toggleDraft(Prompt $prompt): RedirectResponse
   {
       $this->authorize('update', $prompt);
       $prompt->update(['is_draft' => !$prompt->is_draft]);
       return back()->with('success', 'Statut du brouillon mis à jour.');
   }

   public function togglePublic(Prompt $prompt): RedirectResponse
   {
       $this->authorize('update', $prompt);
       $prompt->update(['is_public' => !$prompt->is_public]);
       return back()->with('success', 'Visibilité mise à jour.');
   }

}
