<?php

namespace App\Http\Controllers;

use App\Models\AiAction;
use Illuminate\Http\Request;

class AiActionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $actions = AiAction::with(['interaction', 'reviewer', 'target', 'actionType'])->paginate(15);
        return view('ai.action.index', compact('actions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.action.create');
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
            'ai_interaction_id' => 'required|exists:ai_interactions,id',
            'action_type' => 'required|string',
            'target_type' => 'required|string',
            'target_id' => 'required|integer',
            'field_name' => 'nullable|string',
            'original_data' => 'nullable|json',
            'modified_data' => 'nullable|json',
            'explanation' => 'nullable|string',
            'metadata' => 'nullable|json',
            'status' => 'required|string',
        ]);

        AiAction::create($validated);

        return redirect()->route('ai.action.index')->with('success', 'AI Action created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiAction  $aiAction
     * @return \Illuminate\Http\Response
     */
    public function show(AiAction $aiAction)
    {
        return view('ai.action.show', compact('aiAction'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiAction  $aiAction
     * @return \Illuminate\Http\Response
     */
    public function edit(AiAction $aiAction)
    {
        return view('ai.action.edit', compact('aiAction'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiAction  $aiAction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiAction $aiAction)
    {
        $validated = $request->validate([
            'ai_interaction_id' => 'required|exists:ai_interactions,id',
            'action_type' => 'required|string',
            'target_type' => 'required|string',
            'target_id' => 'required|integer',
            'field_name' => 'nullable|string',
            'original_data' => 'nullable|json',
            'modified_data' => 'nullable|json',
            'explanation' => 'nullable|string',
            'metadata' => 'nullable|json',
            'status' => 'required|string',
        ]);

        $aiAction->update($validated);

        return redirect()->route('ai.action.index')->with('success', 'AI Action updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiAction  $aiAction
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiAction $aiAction)
    {
        $aiAction->delete();

        return redirect()->route('ai.action.index')->with('success', 'AI Action deleted successfully');
    }
}
