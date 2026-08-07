<?php

namespace App\Http\Controllers;

use App\Models\AiSkill;
use App\Services\AI\AiSkillService;
use Illuminate\Http\Request;

class AiSkillController extends Controller
{
    private AiSkillService $skills;

    public function __construct(AiSkillService $skills)
    {
        $this->skills = $skills;
    }

    public function install(Request $request)
    {
        $request->validate([
            'skill_zip' => 'required|file',
        ]);

        try {
            $skill = $this->skills->installFromZip($request->file('skill_zip'));

            return redirect()->route('ai-search.resources', ['tab' => 'skills'])
                ->with('success', "Skill « {$skill->name} » installé avec succès.");
        } catch (\Throwable $e) {
            return redirect()->route('ai-search.resources', ['tab' => 'skills'])
                ->with('error', $e->getMessage());
        }
    }

    public function toggle(AiSkill $skill)
    {
        $skill->update(['enabled' => !$skill->enabled]);

        return redirect()->route('ai-search.resources', ['tab' => 'skills'])
            ->with('success', "Skill « {$skill->name} » " . ($skill->enabled ? 'activé' : 'désactivé') . '.');
    }

    public function destroy(AiSkill $skill)
    {
        try {
            $this->skills->delete($skill);
            return redirect()->route('ai-search.resources', ['tab' => 'skills'])
                ->with('success', 'Skill supprimé.');
        } catch (\Throwable $e) {
            return redirect()->route('ai-search.resources', ['tab' => 'skills'])
                ->with('error', $e->getMessage());
        }
    }
}
