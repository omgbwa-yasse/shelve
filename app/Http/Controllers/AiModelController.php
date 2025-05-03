<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use Illuminate\Http\Request;

class AiModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $models = AiModel::paginate(15);
        return view('ai.model.index', compact('models'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.model.create');
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
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'version' => 'required|string|max:50',
            'api_type' => 'required|string|max:50',
            'capabilities' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        AiModel::create($validated);

        return redirect()->route('ai.model.index')->with('success', 'AI Model created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function show(AiModel $aiModel)
    {
        return view('ai.model.show', compact('aiModel'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function edit(AiModel $aiModel)
    {
        return view('ai.model.edit', compact('aiModel'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiModel $aiModel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'version' => 'required|string|max:50',
            'api_type' => 'required|string|max:50',
            'capabilities' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $aiModel->update($validated);

        return redirect()->route('ai.model.index')->with('success', 'AI Model updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiModel  $aiModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiModel $aiModel)
    {
        $aiModel->delete();

        return redirect()->route('ai.model.index')->with('success', 'AI Model deleted successfully');
    }
}
