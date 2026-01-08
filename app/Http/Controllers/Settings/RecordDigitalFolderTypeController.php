<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalFolderType;
use App\Models\MetadataTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecordDigitalFolderTypeController extends Controller
{
    /**
     * Display a listing of the folder types.
     */
    public function index(Request $request)
    {
        $query = RecordDigitalFolderType::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'display_order');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $folderTypes = $query->paginate(20);

        return view('settings.records.digital-folders.index', compact('folderTypes'));
    }

    /**
     * Show the form for creating a new folder type.
     */
    public function create()
    {
        $metadataTemplates = MetadataTemplate::active()->orderBy('name')->get();

        return view('settings.records.digital-folders.create', compact('metadataTemplates'));
    }

    /**
     * Store a newly created folder type in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:record_digital_folder_types,code|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'code_prefix' => 'nullable|string|max:10',
            'code_pattern' => 'nullable|string|max:50',
            'default_access_level' => 'nullable|in:public,internal,confidential,secret',
            'requires_approval' => 'boolean',
            'metadata_template_id' => 'nullable|exists:metadata_templates,id',
            'mandatory_metadata' => 'nullable|array',
            'allowed_document_types' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $folderType = RecordDigitalFolderType::create(array_merge($validated, [
            'requires_approval' => $request->boolean('requires_approval', false),
            'is_active' => $request->boolean('is_active', true),
            'is_system' => false,
            'display_order' => RecordDigitalFolderType::max('display_order') + 1,
        ]));

        return redirect()
            ->route('settings.folder-types.show', $folderType)
            ->with('success', 'Type de dossier créé avec succès.');
    }

    /**
     * Display the specified folder type.
     */
    public function show(RecordDigitalFolderType $folderType)
    {
        $folderType->load([
            'metadataProfiles.metadataDefinition',
            'metadataTemplate'
        ]);

        $foldersCount = $folderType->folders()->count();

        return view('settings.records.digital-folders.show', compact('folderType', 'foldersCount'));
    }

    /**
     * Show the form for editing the specified folder type.
     */
    public function edit(RecordDigitalFolderType $folderType)
    {
        $metadataTemplates = MetadataTemplate::active()->orderBy('name')->get();

        return view('settings.records.digital-folders.edit', compact('folderType', 'metadataTemplates'));
    }

    /**
     * Update the specified folder type in storage.
     */
    public function update(Request $request, RecordDigitalFolderType $folderType)
    {
        // Prevent system types from being modified
        if ($folderType->is_system) {
            return back()->with('error', 'Les types système ne peuvent pas être modifiés.');
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:record_digital_folder_types,code,' . $folderType->id . '|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'code_prefix' => 'nullable|string|max:10',
            'code_pattern' => 'nullable|string|max:50',
            'default_access_level' => 'nullable|in:public,internal,confidential,secret',
            'requires_approval' => 'boolean',
            'metadata_template_id' => 'nullable|exists:metadata_templates,id',
            'mandatory_metadata' => 'nullable|array',
            'allowed_document_types' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $folderType->update(array_merge($validated, [
            'requires_approval' => $request->boolean('requires_approval', false),
            'is_active' => $request->boolean('is_active', true),
        ]));

        return redirect()
            ->route('settings.folder-types.show', $folderType)
            ->with('success', 'Type de dossier mis à jour avec succès.');
    }

    /**
     * Remove the specified folder type from storage.
     */
    public function destroy(RecordDigitalFolderType $folderType)
    {
        // Prevent system types from being deleted
        if ($folderType->is_system) {
            return back()->with('error', 'Les types système ne peuvent pas être supprimés.');
        }

        // Check if type has associated folders
        if ($folderType->folders()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce type car il y a des dossiers associés.');
        }

        $folderType->delete();

        return redirect()
            ->route('settings.folder-types.index')
            ->with('success', 'Type de dossier supprimé avec succès.');
    }

    /**
     * Update display order (for drag & drop sorting).
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'types' => 'required|array',
            'types.*' => 'required|integer|exists:record_digital_folder_types,id',
        ]);

        foreach ($validated['types'] as $index => $typeId) {
            RecordDigitalFolderType::where('id', $typeId)->update([
                'display_order' => $index + 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordre d\'affichage mis à jour.',
        ]);
    }
}
