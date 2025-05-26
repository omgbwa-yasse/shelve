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
        $actionTypes = \App\Models\AiActionType::where('is_active', true)->get();
        return view('ai.training-data.index', compact('trainingData', 'actionTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $actionTypes = \App\Models\AiActionType::where('is_active', true)->get();
        return view('ai.training-data.create', compact('actionTypes'));
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
            'validated_by' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_validated'] = $request->boolean('is_validated', false);

        AiTrainingData::create($validated);

        return redirect()->route('ai.training-data.index')
            ->with('success', __('training_data_created_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiTrainingData  $aiTrainingData
     * @return \Illuminate\Http\Response
     */
    public function show(AiTrainingData $aiTrainingData)
    {
        return view('ai.training-data.show', compact('aiTrainingData'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiTrainingData  $aiTrainingData
     * @return \Illuminate\Http\Response
     */
    public function edit(AiTrainingData $aiTrainingData)
    {
        $actionTypes = \App\Models\AiActionType::where('is_active', true)->get();
        return view('ai.training-data.edit', compact('aiTrainingData', 'actionTypes'));
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
        ]);

        // Si la validation est activée et n'était pas déjà validée
        if ($request->boolean('is_validated') && !$aiTrainingData->is_validated) {
            $validated['validated_by'] = auth()->id();
        }

        $aiTrainingData->update($validated);

        return redirect()->route('ai.training-data.index')
            ->with('success', __('training_data_updated_successfully'));
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

        return redirect()->route('ai.training-data.index')
            ->with('success', __('training_data_deleted_successfully'));
    }
}
