<?php

namespace App\Http\Controllers;

use App\Models\AiTemplate;
use App\Models\Prompt;
use App\Services\AI\AiSkillService;

class AiResourceController extends Controller
{
    public function index()
    {
        $skillService = app(AiSkillService::class);
        $skillService->ensureDirectories();

        $skills = array_merge($skillService->systemSkills(), $skillService->customSkills());
        $prompts = Prompt::with(['user', 'organisation'])->orderBy('is_system', 'desc')->orderBy('title')->get();
        $templates = AiTemplate::with('creator')->orderByDesc('created_at')->get();

        $promptsCount = $prompts->count();
        $customPrompts = $prompts->where('is_system', false);

        return view('ai-search.resources', compact('skills', 'prompts', 'customPrompts', 'templates', 'promptsCount'));
    }
}
