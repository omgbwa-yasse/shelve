<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalFolder;
use App\Services\RecordDigitalFolderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    protected RecordDigitalFolderService $folderService;

    public function __construct(RecordDigitalFolderService $folderService)
    {
        $this->middleware('auth');
        $this->folderService = $folderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $viewMode = $request->get('view', 'tree'); // tree or list

        $query = RecordDigitalFolder::with(['parent', 'children'])
            ->withCount('documents');

        // Filters
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($viewMode === 'list') {
            $folders = $query->paginate(20);
        } else {
            $folders = $query->get();
        }

        $rootFolders = RecordDigitalFolder::whereNull('parent_id')
            ->withCount('documents')
            ->get();

        return view('folders.index', compact('folders', 'rootFolders', 'viewMode'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $parentId = $request->get('parent_id');
        $parentFolder = $parentId ? RecordDigitalFolder::find($parentId) : null;

        $allFolders = RecordDigitalFolder::orderBy('name')->get();

        return view('folders.create', compact('parentFolder', 'allFolders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:record_digital_folders,id',
            'organization_id' => 'required|exists:record_organizations,id',
            'type_id' => 'required|exists:record_digital_folder_types,id',
            'metadata' => 'nullable|array',
            'access_level' => 'nullable|string|in:public,internal,confidential,secret',
        ]);

        try {
            $type = \App\Models\RecordDigitalFolderType::findOrFail($validated['type_id']);
            $creator = Auth::user();
            $organisation = \App\Models\Organisation::findOrFail($validated['organization_id']);
            $parent = $validated['parent_id'] ? RecordDigitalFolder::findOrFail($validated['parent_id']) : null;

            $folder = $this->folderService->createFolder(
                $type,
                $validated,
                $creator,
                $organisation,
                $parent
            );

            return redirect()
                ->route('folders.show', $folder->id)
                ->with('success', 'Folder created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error creating folder: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $folder = RecordDigitalFolder::with([
            'parent',
            'children.children',
            'documents',
            'organization',
            'type',
            'creator'
        ])->findOrFail($id);

        // Calculate stats manually
        $stats = [
            'total_documents' => $folder->documents()->count(),
            'total_subfolders' => $folder->children()->count(),
            'total_size' => $folder->documents()->sum('size') ?? 0,
        ];

        // Get subtree
        $tree = $folder->children()->with('children')->get();

        return view('folders.show', compact('folder', 'stats', 'tree'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $folder = RecordDigitalFolder::findOrFail($id);
        $allFolders = RecordDigitalFolder::where('id', '!=', $id)
            ->orderBy('name')
            ->get();

        return view('folders.edit', compact('folder', 'allFolders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $folder = RecordDigitalFolder::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:record_digital_folders,code,' . $id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:record_digital_folders,id',
            'organization_id' => 'required|exists:record_organizations,id',
            'type_id' => 'required|exists:record_digital_folder_types,id',
        ]);

        try {
            $folder->update($validated);

            return redirect()
                ->route('folders.show', $folder->id)
                ->with('success', 'Folder updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error updating folder: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $folder = RecordDigitalFolder::findOrFail($id);

            // Check if folder has children
            if ($folder->children()->count() > 0) {
                return back()->with('error', 'Cannot delete folder with sub-folders. Delete children first.');
            }

            // Check if folder has documents
            if ($folder->documents()->count() > 0) {
                return back()->with('error', 'Cannot delete folder with documents. Remove documents first.');
            }

            $folder->delete();

            return redirect()
                ->route('folders.index')
                ->with('success', 'Folder deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting folder: ' . $e->getMessage());
        }
    }

    /**
     * Move a folder to a new parent
     */
    public function move(Request $request, string $id)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:record_digital_folders,id',
        ]);

        try {
            $folder = RecordDigitalFolder::findOrFail($id);
            $newParent = $validated['parent_id'] ? RecordDigitalFolder::findOrFail($validated['parent_id']) : null;

            $this->folderService->moveFolder($folder, $newParent);

            return response()->json([
                'success' => true,
                'message' => 'Folder moved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get tree data for jstree (API endpoint)
     */
    public function tree(Request $request)
    {
        $folders = RecordDigitalFolder::with('children')
            ->withCount('documents')
            ->get();

        // Transform to jstree format
        $jsTreeData = $this->transformToJsTree($folders);

        return response()->json($jsTreeData);
    }

    /**
     * Transform folder tree to jstree format
     */
    private function transformToJsTree($folders)
    {
        return $folders->map(function($folder) {
            return [
                'id' => $folder->id,
                'parent' => $folder->parent_id ?? '#',
                'text' => $folder->name . ' (' . $folder->code . ')',
                'icon' => 'fas fa-folder',
                'data' => [
                    'code' => $folder->code,
                    'description' => $folder->description,
                    'documents_count' => $folder->documents_count ?? 0,
                ],
                'children' => $folder->children ? $this->transformToJsTree($folder->children) : []
            ];
        });
    }
}
