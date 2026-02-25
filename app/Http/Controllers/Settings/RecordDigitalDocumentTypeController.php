<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolderType;
use App\Models\MetadataTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecordDigitalDocumentTypeController extends Controller
{
    /**
     * Display a listing of the document types.
     */
    public function index(Request $request)
    {
        $query = RecordDigitalDocumentType::query();

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

        $documentTypes = $query->paginate(20);

        return view('settings.records.digital-documents.index', compact('documentTypes'));
    }

    /**
     * Show the form for creating a new document type.
     */
    public function create()
    {
        $metadataTemplates = MetadataTemplate::active()->orderBy('name')->get();
        $folderTypes = RecordDigitalFolderType::active()->orderBy('name')->get();

        return view('settings.records.digital-documents.create', compact('metadataTemplates', 'folderTypes'));
    }

    /**
     * Store a newly created document type in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:record_digital_document_types,code|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'mime_types' => 'nullable|array',
            'max_file_size' => 'nullable|integer|min:1',
            'code_prefix' => 'nullable|string|max:10',
            'code_pattern' => 'nullable|string|max:50',
            'default_access_level' => 'nullable|in:public,internal,confidential,secret',
            'requires_approval' => 'boolean',
            'metadata_template_id' => 'nullable|exists:metadata_templates,id',
            'mandatory_metadata' => 'nullable|array',
            'allowed_folder_types' => 'nullable|array',
            'require_virus_scan' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $documentType = RecordDigitalDocumentType::create(array_merge($validated, [
            'requires_approval' => $request->boolean('requires_approval', false),
            'require_virus_scan' => $request->boolean('require_virus_scan', true),
            'is_active' => $request->boolean('is_active', true),
            'is_system' => false,
            'display_order' => RecordDigitalDocumentType::max('display_order') + 1,
        ]));

        return redirect()
            ->route('settings.document-types.show', $documentType)
            ->with('success', 'Type de document créé avec succès.');
    }

    /**
     * Display the specified document type.
     */
    public function show(RecordDigitalDocumentType $documentType)
    {
        $documentType->load([
            'metadataProfiles.metadataDefinition',
            'metadataTemplate'
        ]);

        $documentsCount = $documentType->documents()->count();

        return view('settings.records.digital-documents.show', compact('documentType', 'documentsCount'));
    }

    /**
     * Show the form for editing the specified document type.
     */
    public function edit(RecordDigitalDocumentType $documentType)
    {
        $metadataTemplates = MetadataTemplate::active()->orderBy('name')->get();
        $folderTypes = RecordDigitalFolderType::active()->orderBy('name')->get();

        return view('settings.records.digital-documents.edit', compact('documentType', 'metadataTemplates', 'folderTypes'));
    }

    /**
     * Update the specified document type in storage.
     */
    public function update(Request $request, RecordDigitalDocumentType $documentType)
    {
        // Prevent system types from being modified
        if ($documentType->is_system) {
            return back()->with('error', 'Les types système ne peuvent pas être modifiés.');
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:record_digital_document_types,code,' . $documentType->id . '|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'mime_types' => 'nullable|array',
            'max_file_size' => 'nullable|integer|min:1',
            'code_prefix' => 'nullable|string|max:10',
            'code_pattern' => 'nullable|string|max:50',
            'default_access_level' => 'nullable|in:public,internal,confidential,secret',
            'requires_approval' => 'boolean',
            'metadata_template_id' => 'nullable|exists:metadata_templates,id',
            'mandatory_metadata' => 'nullable|array',
            'allowed_folder_types' => 'nullable|array',
            'require_virus_scan' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $documentType->update(array_merge($validated, [
            'requires_approval' => $request->boolean('requires_approval', false),
            'require_virus_scan' => $request->boolean('require_virus_scan', true),
            'is_active' => $request->boolean('is_active', true),
        ]));

        return redirect()
            ->route('settings.document-types.show', $documentType)
            ->with('success', 'Type de document mis à jour avec succès.');
    }

    /**
     * Remove the specified document type from storage.
     */
    public function destroy(RecordDigitalDocumentType $documentType)
    {
        // Prevent system types from being deleted
        if ($documentType->is_system) {
            return back()->with('error', 'Les types système ne peuvent pas être supprimés.');
        }

        // Check if type has associated documents
        if ($documentType->documents()->exists()) {
            return back()->with('error', 'Impossible de supprimer ce type car il y a des documents associés.');
        }

        $documentType->delete();

        return redirect()
            ->route('settings.document-types.index')
            ->with('success', 'Type de document supprimé avec succès.');
    }

    /**
     * Update display order (for drag & drop sorting).
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'types' => 'required|array',
            'types.*' => 'required|integer|exists:record_digital_document_types,id',
        ]);

        foreach ($validated['types'] as $index => $typeId) {
            RecordDigitalDocumentType::where('id', $typeId)->update([
                'display_order' => $index + 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ordre d\'affichage mis à jour.',
        ]);
    }
}
