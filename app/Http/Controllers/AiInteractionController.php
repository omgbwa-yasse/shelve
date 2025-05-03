<?php

namespace App\Http\Controllers;

use App\Models\AiInteraction;
use Illuminate\Http\Request;

class AiInteractionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $interactions = AiInteraction::with(['user', 'aiModel', 'actions', 'feedback'])->paginate(15);
        return view('ai.interaction.index', compact('interactions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.interaction.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'ai_model_id' => 'required|exists:ai_models,id',
            'input' => 'required|string',
            'output' => 'nullable|string',
            'parameters' => 'nullable|json',
            'tokens_used' => 'nullable|numeric',
            'module_type' => 'nullable|string',
            'module_id' => 'nullable|integer',
            'status' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        AiInteraction::create($validated);

        return redirect()->route('ai.interaction.index')->with('success', 'AI Interaction created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiInteraction  $aiInteraction
     * @return \Illuminate\Http\Response
     */
    public function show(AiInteraction $aiInteraction)
    {
        return view('ai.interaction.show', compact('aiInteraction'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiInteraction  $aiInteraction
     * @return \Illuminate\Http\Response
     */
    public function edit(AiInteraction $aiInteraction)
    {
        return view('ai.interaction.edit', compact('aiInteraction'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiInteraction  $aiInteraction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiInteraction $aiInteraction)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'ai_model_id' => 'required|exists:ai_models,id',
            'input' => 'required|string',
            'output' => 'nullable|string',
            'parameters' => 'nullable|json',
            'tokens_used' => 'nullable|numeric',
            'module_type' => 'nullable|string',
            'module_id' => 'nullable|integer',
            'status' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        $aiInteraction->update($validated);

        return redirect()->route('ai.interaction.index')->with('success', 'AI Interaction updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiInteraction  $aiInteraction
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiInteraction $aiInteraction)
    {
        $aiInteraction->delete();

        return redirect()->route('ai.interaction.index')->with('success', 'AI Interaction deleted successfully');
    }
}
