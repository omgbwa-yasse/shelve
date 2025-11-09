<?php

namespace App\Http\Controllers;

use App\Models\Workplace;
use App\Models\WorkplaceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Workplace::with(['category', 'owner', 'organisation'])
            ->byOrganisation(Auth::user()->organisation_id);

        // Filters
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $workplaces = $query->latest()->paginate(12);
        $categories = WorkplaceCategory::active()->ordered()->get();

        return view('workplaces.index', compact('workplaces', 'categories'));
    }

    public function create()
    {
        $categories = WorkplaceCategory::active()->ordered()->get();
        return view('workplaces.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:workplace_categories,id',
            'is_public' => 'boolean',
            'allow_external_sharing' => 'boolean',
            'max_members' => 'nullable|integer|min:1',
            'max_storage_mb' => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $workplace = Workplace::create([
                ...$validated,
                'code' => $this->generateWorkplaceCode(),
                'organisation_id' => Auth::user()->organisation_id,
                'owner_id' => Auth::id(),
                'created_by' => Auth::id(),
                'status' => 'active',
            ]);

            // Add creator as owner member
            $workplace->members()->create([
                'user_id' => Auth::id(),
                'role' => 'owner',
                'can_create_folders' => true,
                'can_create_documents' => true,
                'can_delete' => true,
                'can_share' => true,
                'can_invite' => true,
                'joined_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('workplaces.show', $workplace)
                ->with('success', 'Workspace créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la création du workspace'])
                ->withInput();
        }
    }

    public function show(Workplace $workplace)
    {
        $workplace->load([
            'category',
            'owner',
            'members.user',
            'folders.folder',
            'documents.document',
            'activities' => fn($q) => $q->latest()->limit(10)
        ]);

        return view('workplaces.show', compact('workplace'));
    }

    public function edit(Workplace $workplace)
    {
        $categories = WorkplaceCategory::active()->ordered()->get();
        return view('workplaces.edit', compact('workplace', 'categories'));
    }

    public function update(Request $request, Workplace $workplace)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:workplace_categories,id',
            'is_public' => 'boolean',
            'allow_external_sharing' => 'boolean',
            'max_members' => 'nullable|integer|min:1',
            'max_storage_mb' => 'nullable|integer|min:1',
        ]);

        $workplace->update([
            ...$validated,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('workplaces.show', $workplace)
            ->with('success', 'Workspace mis à jour avec succès');
    }

    public function destroy(Workplace $workplace)
    {
        $workplace->delete();
        return redirect()->route('workplaces.index')
            ->with('success', 'Workspace supprimé avec succès');
    }

    public function archive(Workplace $workplace)
    {
        $workplace->update(['status' => 'archived']);
        return back()->with('success', 'Workspace archivé avec succès');
    }

    public function settings(Workplace $workplace)
    {
        return view('workplaces.settings', compact('workplace'));
    }

    private function generateWorkplaceCode(): string
    {
        $year = date('Y');
        $lastWorkplace = Workplace::whereYear('created_at', $year)
            ->orderBy('code', 'desc')
            ->first();

        if ($lastWorkplace && preg_match('/WP-' . $year . '-(\d+)/', $lastWorkplace->code, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return sprintf('WP-%s-%04d', $year, $number);
    }
}
