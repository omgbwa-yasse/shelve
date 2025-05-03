<?php

namespace App\Http\Controllers;

use App\Models\AiTrainingData;
use Illuminate\Http\Request;

class AiTrainingDataController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $trainingData = AiTrainingData::with(['actionType', 'creator', 'validator'])->paginate(15);
        return view('ai.trainingdata.index', compact('trainingData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.trainingdata.create');
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
            'action_type_id' => 'required|exists:ai_action_types,id',
            'input' => 'required|string',
            'expected_output' => 'required|string',
            'is_validated' => 'boolean',
            'created_by' => 'required|exists:users,id',
            'validated_by' => 'nullable|exists:users,id',
        ]);

        AiTrainingData::create($validated);

        return redirect()->route('ai.trainingdata.index')->with('success', 'AI Training Data created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiTrainingData  $aiTrainingData
     * @return \Illuminate\Http\Response
     */
    public function show(AiTrainingData $aiTrainingData)
    {
        return view('ai.trainingdata.show', compact('aiTrainingData'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiTrainingData  $aiTrainingData
     * @return \Illuminate\Http\Response
     */
    public function edit(AiTrainingData $aiTrainingData)
    {
        return view('ai.trainingdata.edit', compact('aiTrainingData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiTrainingData  $aiTrainingData
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiTrainingData $aiTrainingData)
    {
        $validated = $request->validate([
            'action_type_id' => 'required|exists:ai_action_types,id',
            'input' => 'required|string',
            'expected_output' => 'required|string',
            'is_validated' => 'boolean',
            'created_by' => 'required|exists:users,id',
            'validated_by' => 'nullable|exists:users,id',
        ]);

        $aiTrainingData->update($validated);

        return redirect()->route('ai.trainingdata.index')->with('success', 'AI Training Data updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiTrainingData  $aiTrainingData
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiTrainingData $aiTrainingData)
    {
        $aiTrainingData->delete();

        return redirect()->route('ai.trainingdata.index')->with('success', 'AI Training Data deleted successfully');
    }
}
