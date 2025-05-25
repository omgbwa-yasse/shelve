<?php

namespace App\Http\Controllers;

use App\Models\AiIntegration;
use Illuminate\Http\Request;

class AiIntegrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $integrations = AiIntegration::with(['actionType', 'promptTemplate'])->paginate(15);
        $actionTypes = \App\Models\AiActionType::where('is_active', true)->get();
        $promptTemplates = \App\Models\AiPromptTemplate::where('is_active', true)->get();
        return view('ai.integrations.index', compact('integrations', 'actionTypes', 'promptTemplates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $actionTypes = \App\Models\AiActionType::where('is_active', true)->get();
        $promptTemplates = \App\Models\AiPromptTemplate::where('is_active', true)->get();
        return view('ai.integrations.create', compact('actionTypes', 'promptTemplates'));
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
            'module_name' => 'required|string|max:255',
            'event_name' => 'required|string|max:255',
            'hook_type' => 'required|string|max:255',
            'action_type_id' => 'required|exists:ai_action_types,id',
            'ai_prompt_template_id' => 'required|exists:ai_prompt_templates,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'configuration' => 'nullable|json',
        ]);

        AiIntegration::create($validated);

        return redirect()->route('ai.integrations.index')
            ->with('success', __('integration_created_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiIntegration  $aiIntegration
     * @return \Illuminate\Http\Response
     */
    public function show(AiIntegration $aiIntegration)
    {
        return view('ai.integrations.show', compact('aiIntegration'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiIntegration  $aiIntegration
     * @return \Illuminate\Http\Response
     */
    public function edit(AiIntegration $aiIntegration)
    {
        $actionTypes = \App\Models\AiActionType::where('is_active', true)->get();
        $promptTemplates = \App\Models\AiPromptTemplate::where('is_active', true)->get();
        return view('ai.integrations.edit', compact('aiIntegration', 'actionTypes', 'promptTemplates'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiIntegration  $aiIntegration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiIntegration $aiIntegration)
    {
        $validated = $request->validate([
            'module_name' => 'required|string|max:255',
            'event_name' => 'required|string|max:255',
            'hook_type' => 'required|string|max:255',
            'action_type_id' => 'required|exists:ai_action_types,id',
            'ai_prompt_template_id' => 'required|exists:ai_prompt_templates,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'configuration' => 'nullable|json',
        ]);

        $aiIntegration->update($validated);

        return redirect()->route('ai.integrations.index')
            ->with('success', __('integration_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiIntegration  $aiIntegration
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiIntegration $aiIntegration)
    {
        $aiIntegration->delete();

        return redirect()->route('ai.integrations.index')
            ->with('success', __('integration_deleted_successfully'));
    }
}
