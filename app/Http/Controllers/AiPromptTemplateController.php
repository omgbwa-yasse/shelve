<?php

namespace App\Http\Controllers;

use App\Models\AiPromptTemplate;
use Illuminate\Http\Request;

class AiPromptTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $promptTemplates = AiPromptTemplate::with(['actionType', 'creator'])->paginate(15);
        return view('ai.prompttemplate.index', compact('promptTemplates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.prompttemplate.create');
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
            'description' => 'nullable|string',
            'template_content' => 'required|string',
            'action_type_id' => 'required|exists:ai_action_types,id',
            'variables' => 'nullable|json',
            'created_by' => 'required|exists:users,id',
            'is_active' => 'boolean',
        ]);

        AiPromptTemplate::create($validated);

        return redirect()->route('ai.prompttemplate.index')->with('success', 'AI Prompt Template created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiPromptTemplate  $aiPromptTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(AiPromptTemplate $aiPromptTemplate)
    {
        return view('ai.prompttemplate.show', compact('aiPromptTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiPromptTemplate  $aiPromptTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit(AiPromptTemplate $aiPromptTemplate)
    {
        return view('ai.prompttemplate.edit', compact('aiPromptTemplate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiPromptTemplate  $aiPromptTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiPromptTemplate $aiPromptTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_content' => 'required|string',
            'action_type_id' => 'required|exists:ai_action_types,id',
            'variables' => 'nullable|json',
            'created_by' => 'required|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $aiPromptTemplate->update($validated);

        return redirect()->route('ai.prompttemplate.index')->with('success', 'AI Prompt Template updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiPromptTemplate  $aiPromptTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiPromptTemplate $aiPromptTemplate)
    {
        $aiPromptTemplate->delete();

        return redirect()->route('ai.prompttemplate.index')->with('success', 'AI Prompt Template deleted successfully');
    }
}
