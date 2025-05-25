<?php

namespace App\Http\Controllers;

use App\Models\AiPromptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $actionTypes = \App\Models\AiActionType::where('is_active', true)->get();
        return view('ai.prompt-templates.index', compact('promptTemplates', 'actionTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $actionTypes = \App\Models\AiActionType::where('is_active', true)->get();
        return view('ai.prompt-templates.create', compact('actionTypes'));
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
            'template' => 'required|string',
            'action_type_id' => 'required|exists:ai_action_types,id',
            'variables' => 'nullable|json',
            'category' => 'required|in:text,image,code,data',
            'status' => 'required|in:active,inactive',
            'is_system' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        AiPromptTemplate::create($validated);

        return redirect()->route('ai.prompt-templates.index')
            ->with('success', __('template_created_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiPromptTemplate  $aiPromptTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(AiPromptTemplate $promptTemplate)
    {
        return view('ai.prompt-templates.show', compact('promptTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiPromptTemplate  $aiPromptTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit(AiPromptTemplate $promptTemplate)
    {
        $actionTypes = \App\Models\AiActionType::where('is_active', true)->get();
        return view('ai.prompt-templates.edit', compact('promptTemplate', 'actionTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiPromptTemplate  $aiPromptTemplate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiPromptTemplate $promptTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template' => 'required|string',
            'action_type_id' => 'required|exists:ai_action_types,id',
            'variables' => 'nullable|json',
            'category' => 'required|in:text,image,code,data',
            'status' => 'required|in:active,inactive',
            'is_system' => 'boolean',
        ]);

        $promptTemplate->update($validated);

        return redirect()->route('ai.prompt-templates.index')
            ->with('success', __('template_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiPromptTemplate  $aiPromptTemplate
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiPromptTemplate $promptTemplate)
    {
        $promptTemplate->delete();

        return redirect()->route('ai.prompt-templates.index')
            ->with('success', __('template_deleted_successfully'));
    }
}
