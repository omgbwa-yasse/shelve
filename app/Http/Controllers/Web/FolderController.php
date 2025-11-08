<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalFolderType;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RecordDigitalFolder::with(['type', 'parent', 'creator', 'organisation', 'assignedUser'])
            ->withCount(['children', 'documents']);

        // Filtres
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } elseif ($request->get('show_roots', false)) {
            $query->whereNull('parent_id');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $folders = $query->latest()->paginate(20);
        $types = RecordDigitalFolderType::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();

        return view('repositories.folders.index', compact('folders', 'types', 'organisations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $types = RecordDigitalFolderType::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        // Récupérer les dossiers parents potentiels
        $parentFolders = RecordDigitalFolder::with('type')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $parentId = $request->get('parent_id');

        return view('repositories.folders.create', compact('types', 'organisations', 'users', 'parentFolders', 'parentId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_id' => 'required|exists:record_digital_folder_types,id',
            'parent_id' => 'nullable|exists:record_digital_folders,id',
            'organisation_id' => 'required|exists:organisations,id',
            'assigned_to' => 'nullable|exists:users,id',
            'access_level' => 'required|in:public,internal,confidential,secret',
            'status' => 'required|in:active,archived,closed',
            'requires_approval' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'metadata' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $type = RecordDigitalFolderType::findOrFail($validated['type_id']);

            // Générer le code automatiquement
            $code = $type->generateCode();

            $folder = RecordDigitalFolder::create(array_merge($validated, [
                'code' => $code,
                'creator_id' => Auth::id(),
                'requires_approval' => $request->boolean('requires_approval', false),
            ]));

            // Mettre à jour les statistiques du parent si applicable
            if ($folder->parent) {
                $folder->parent->updateStatistics();
            }

            DB::commit();

            return redirect()
                ->route('folders.show', $folder)
                ->with('success', 'Dossier créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du dossier : ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RecordDigitalFolder $folder)
    {
        $folder->load([
            'type',
            'parent',
            'children.type',
            'documents.type',
            'creator',
            'organisation',
            'assignedUser',
            'approver'
        ]);

        $folder->loadCount(['children', 'documents']);

        // Récupérer le chemin d'arborescence
        $breadcrumb = $folder->getAncestors()->reverse()->push($folder);

        return view('repositories.folders.show', compact('folder', 'breadcrumb'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RecordDigitalFolder $folder)
    {
        $types = RecordDigitalFolderType::orderBy('name')->get();
        $organisations = Organisation::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        // Exclure le dossier lui-même et ses descendants de la liste des parents possibles
        $excludedIds = $folder->getDescendants()->pluck('id')->push($folder->id);
        $parentFolders = RecordDigitalFolder::with('type')
            ->where('status', 'active')
            ->whereNotIn('id', $excludedIds)
            ->orderBy('name')
            ->get();

        return view('repositories.folders.edit', compact('folder', 'types', 'organisations', 'users', 'parentFolders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecordDigitalFolder $folder)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_id' => 'required|exists:record_digital_folder_types,id',
            'parent_id' => 'nullable|exists:record_digital_folders,id',
            'organisation_id' => 'required|exists:organisations,id',
            'assigned_to' => 'nullable|exists:users,id',
            'access_level' => 'required|in:public,internal,confidential,secret',
            'status' => 'required|in:active,archived,closed',
            'requires_approval' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'metadata' => 'nullable|array',
        ]);

        // Vérifier qu'on ne crée pas une boucle dans la hiérarchie
        if ($validated['parent_id']) {
            $parentFolder = RecordDigitalFolder::find($validated['parent_id']);
            if ($parentFolder && ($parentFolder->id === $folder->id || $folder->getDescendants()->contains($parentFolder))) {
                return back()
                    ->withInput()
                    ->with('error', 'Impossible de déplacer un dossier dans lui-même ou dans l\'un de ses sous-dossiers.');
            }
        }

        DB::beginTransaction();
        try {
            $oldParentId = $folder->parent_id;

            $folder->update(array_merge($validated, [
                'requires_approval' => $request->boolean('requires_approval', false),
            ]));

            // Mettre à jour les statistiques si le parent a changé
            if ($oldParentId !== $folder->parent_id) {
                if ($oldParentId) {
                    $oldParent = RecordDigitalFolder::find($oldParentId);
                    $oldParent?->updateStatistics();
                }
                if ($folder->parent) {
                    $folder->parent->updateStatistics();
                }
            }

            DB::commit();

            return redirect()
                ->route('folders.show', $folder)
                ->with('success', 'Dossier mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour du dossier : ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecordDigitalFolder $folder)
    {
        // Vérifier si le dossier contient des documents ou des sous-dossiers
        if ($folder->documents()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer un dossier contenant des documents.');
        }

        if ($folder->children()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer un dossier contenant des sous-dossiers.');
        }

        DB::beginTransaction();
        try {
            $parentId = $folder->parent_id;
            $folder->delete();

            // Mettre à jour les statistiques du parent
            if ($parentId) {
                $parent = RecordDigitalFolder::find($parentId);
                $parent?->updateStatistics();
            }

            DB::commit();

            return redirect()
                ->route('folders.index')
                ->with('success', 'Dossier supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression du dossier : ' . $e->getMessage());
        }
    }

    /**
     * Move a folder to a new parent
     */
    public function move(Request $request, RecordDigitalFolder $folder)
    {
        $validated = $request->validate([
            'new_parent_id' => 'nullable|exists:record_digital_folders,id',
        ]);

        $newParentId = $validated['new_parent_id'] ?? null;

        // Vérifier qu'on ne crée pas une boucle
        if ($newParentId) {
            $newParent = RecordDigitalFolder::find($newParentId);
            if ($newParent->id === $folder->id || $folder->getDescendants()->contains($newParent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de déplacer un dossier dans lui-même ou dans l\'un de ses sous-dossiers.'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $oldParentId = $folder->parent_id;

            $folder->update(['parent_id' => $newParentId]);

            // Mettre à jour les statistiques
            if ($oldParentId) {
                $oldParent = RecordDigitalFolder::find($oldParentId);
                $oldParent?->updateStatistics();
            }
            if ($newParentId) {
                $newParent = RecordDigitalFolder::find($newParentId);
                $newParent?->updateStatistics();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dossier déplacé avec succès.',
                'folder' => $folder->fresh(['parent', 'type'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du déplacement : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get folder tree data (for tree view component)
     */
    public function tree(Request $request)
    {
        $organisationId = $request->get('organisation_id');
        $typeId = $request->get('type_id');

        $query = RecordDigitalFolder::with(['type', 'children'])
            ->withCount(['children', 'documents'])
            ->where('status', 'active');

        if ($organisationId) {
            $query->where('organisation_id', $organisationId);
        }

        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        // Récupérer uniquement les dossiers racine
        $rootFolders = $query->whereNull('parent_id')->orderBy('name')->get();

        // Transformer en format arborescent pour le frontend
        $tree = $this->buildTree($rootFolders);

        return response()->json([
            'success' => true,
            'tree' => $tree
        ]);
    }

    /**
     * Build tree structure recursively
     */
    private function buildTree($folders)
    {
        return $folders->map(function ($folder) {
            return [
                'id' => $folder->id,
                'code' => $folder->code,
                'name' => $folder->name,
                'type' => $folder->type->name ?? null,
                'type_code' => $folder->type->code ?? null,
                'documents_count' => $folder->documents_count,
                'subfolders_count' => $folder->children_count,
                'total_size' => $folder->total_size,
                'total_size_human' => $folder->total_size_human,
                'status' => $folder->status,
                'access_level' => $folder->access_level,
                'children' => $folder->children ? $this->buildTree($folder->children) : []
            ];
        });
    }
}
