<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Prompt;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AgentController extends Controller
{
   public function index(): View
   {
       $agents = Agent::with(['user', 'prompt'])
           ->when( function ($query) {
               return $query->where('user_id', Auth::id())
                   ->orWhere('is_public', true);
           })
           ->latest()
           ->paginate(10);
       return view('agents.index', compact('agents'));
   }


   public function create(): View
   {
       $prompts = Prompt::where('user_id', Auth::id())
           ->orWhere('is_public', true)
           ->get();
       return view('agents.create', compact('prompts'));
   }



   public function store(Request $request): RedirectResponse
   {
       $validated = $request->validate([
           'name' => 'required|string|max:150|unique:ai_agents',
           'description' => 'required|string',
           'date_type' => 'required|in:' . implode(',', Agent::DATE_TYPES),
           'date_start' => 'required_if:date_type,start_only,range|nullable|date',
           'date_end' => 'required_if:date_type,range|nullable|date|after:date_start',
           'date_exact' => 'required_if:date_type,exact|nullable|date',
           'frequence_type' => 'required|in:' . implode(',', Agent::FREQUENCY_TYPES),
           'frequence_value' => 'required|integer|min:1',
           'prompt_id' => 'required|exists:prompts,id',
           'is_public' => 'boolean',
           'is_trained' => 'boolean'
       ]);
       $agent = Agent::create($validated);
       return redirect()
           ->route('agents.show', $agent)
           ->with('success', 'Agent AI créé avec succès.');
   }




   public function show(Agent $Agent): View
   {
       $Agent->load(['user', 'prompt']);
       return view('agents.show', compact('Agent'));
   }



   public function edit(Agent $Agent): View
   {
       $prompts = Prompt::where('user_id', Auth::id())
           ->orWhere('is_public', true)
           ->get();
       return view('agents.edit', [
           'Agent' => $Agent,
           'prompts' => $prompts
       ]);
   }

   public function update(Request $request, Agent $Agent): RedirectResponse
   {
       $this->authorize('update', $Agent);

       $validated = $request->validate([
           'name' => 'required|string|max:150|unique:ai_agents,name,' . $Agent->id,
           'description' => 'required|string',
           'date_type' => 'required|in:' . implode(',', Agent::DATE_TYPES),
           'date_start' => 'required_if:date_type,start_only,range|nullable|date',
           'date_end' => 'required_if:date_type,range|nullable|date|after:date_start',
           'date_exact' => 'required_if:date_type,exact|nullable|date',
           'frequence_type' => 'required|in:' . implode(',', Agent::FREQUENCY_TYPES),
           'frequence_value' => 'required|integer|min:1',
           'prompt_id' => 'required|exists:prompts,id',
           'is_public' => 'boolean',
           'is_trained' => 'boolean'
       ]);

       $Agent->update($validated);

       return redirect()
           ->route('agents.show', $Agent)
           ->with('success', 'Agent AI mis à jour avec succès.');
   }


   public function destroy(Agent $Agent): RedirectResponse
   {
       $this->authorize('delete', $Agent);

       $Agent->delete();

       return redirect()
           ->route('agents.index')
           ->with('success', 'Agent AI supprimé avec succès.');
   }


   // Méthodes additionnelles
   public function toggleStatus(Agent $Agent): RedirectResponse
   {
       $this->authorize('update', $Agent);

       $Agent->update(['is_trained' => !$Agent->is_trained]);

       return back()->with('success', 'Statut de l\'agent mis à jour.');
   }

   public function toggleVisibility(Agent $Agent): RedirectResponse
   {
       $this->authorize('update', $Agent);

       $Agent->update(['is_public' => !$Agent->is_public]);

       return back()->with('success', 'Visibilité de l\'agent mise à jour.');
   }
}
