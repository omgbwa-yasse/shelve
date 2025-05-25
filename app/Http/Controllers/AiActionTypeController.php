<?php

namespace App\Http\Controllers;

use App\Models\AiActionType;
use Illuminate\Http\Request;

class AiActionTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $actionTypes = AiActionType::paginate(15);
        return view('ai.action-types.index', compact('actionTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.action-types.create');
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
            'name' => 'required|string|max:255|unique:ai_action_types',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'required_fields' => 'nullable|json',
            'optional_fields' => 'nullable|json',
            'validation_rules' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        AiActionType::create($validated);

        return redirect()->route('ai.action-types.index')->with('success', 'AI Action Type created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiActionType  $aiActionType
     * @return \Illuminate\Http\Response
     */
    public function show(AiActionType $aiActionType)
    {
        return view('ai.action-types.show', compact('aiActionType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiActionType  $aiActionType
     * @return \Illuminate\Http\Response
     */
    public function edit(AiActionType $aiActionType)
    {
        return view('ai.action-types.edit', compact('aiActionType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiActionType  $aiActionType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiActionType $aiActionType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ai_action_types,name,' . $aiActionType->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'required_fields' => 'nullable|json',
            'optional_fields' => 'nullable|json',
            'validation_rules' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $aiActionType->update($validated);

        return redirect()->route('ai.action-types.index')->with('success', 'AI Action Type updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiActionType  $aiActionType
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiActionType $aiActionType)
    {
        $aiActionType->delete();

        return redirect()->route('ai.action-types.index')->with('success', 'AI Action Type deleted successfully');
    }
}
