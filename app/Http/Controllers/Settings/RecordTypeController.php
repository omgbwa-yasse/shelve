<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\MetadataDefinition;
use App\Models\RecordType;
use App\Models\ReferenceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Configuration des typologies unifiées (record_types).
 *
 * Remplaçable à terme des RecordDigitalFolderTypeController / RecordDigitalDocumentTypeController :
 * un seul catalogue, ancré sur reference_lists, avec is_container (dossier/document).
 */
class RecordTypeController extends Controller
{
    public function index()
    {
        $types = RecordType::with('referenceList', 'parent')
            ->withCount('metadataProfiles')
            ->orderBy('is_container', 'desc')
            ->orderBy('display_order')
            ->paginate(20);

        return view('settings.record-types.index', compact('types'));
    }

    public function create()
    {
        return view('settings.record-types.create', [
            'referenceLists' => ReferenceList::all(),
            'parents' => RecordType::active()->whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateType($request);

        $data['created_by'] = Auth::id();

        RecordType::create($data);

        return redirect()->route('settings.record-types.index')
            ->with('success', 'Typologie créée avec succès.');
    }

    public function edit(RecordType $recordType)
    {
        $attachedProfiles = $recordType->metadataProfiles()
            ->with('metadataDefinition')
            ->ordered()
            ->get();

        $attachedDefinitionIds = $attachedProfiles->pluck('metadata_definition_id');

        return view('settings.record-types.edit', [
            'recordType' => $recordType,
            'referenceLists' => ReferenceList::all(),
            'parents' => RecordType::active()->where('id', '!=', $recordType->id)->orderBy('name')->get(),
            'attachedProfiles' => $attachedProfiles,
            'availableDefinitions' => MetadataDefinition::active()
                ->whereNotIn('id', $attachedDefinitionIds)
                ->orderBy('is_system', 'desc')
                ->orderBy('name')
                ->get(),
            'roles' => \App\Models\Role::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, RecordType $recordType)
    {
        $data = $this->validateType($request);

        $data['updated_by'] = Auth::id();

        $recordType->update($data);

        return redirect()->route('settings.record-types.index')
            ->with('success', 'Typologie mise à jour.');
    }

    public function destroy(RecordType $recordType)
    {
        $recordCount = \App\Models\Record::withTrashed()->where('type_id', $recordType->id)->count();

        if ($recordCount > 0) {
            return redirect()->route('settings.record-types.index')
                ->with('error', "Typologie utilisée par {$recordCount} notice(s) : suppression impossible. Réaffectez d'abord les notices.");
        }

        if ($recordType->children()->count() > 0) {
            return redirect()->route('settings.record-types.index')
                ->with('error', 'Cette typologie possède des sous-typologies : suppression impossible.');
        }

        try {
            $recordType->delete();

            return redirect()->route('settings.record-types.index')
                ->with('success', 'Typologie supprimée.');
        } catch (\Exception $e) {
            return redirect()->route('settings.record-types.index')
                ->with('error', 'Erreur : '.$e->getMessage());
        }
    }

    public function restore($id)
    {
        $recordType = RecordType::withTrashed()->findOrFail($id);

        $recordType->restore();

        return redirect()->route('settings.record-types.index')
            ->with('success', 'Typologie restaurée.');
    }

    private function validateType(Request $request): array
    {
        return $request->validate([
            'code' => 'required|string|max:50|unique:record_types,code,'.($request->route('recordType')->id ?? 'NULL'),
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:record_types,id',
            'reference_list_id' => 'nullable|exists:reference_lists,id',
            'is_container' => 'boolean',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'code_prefix' => 'nullable|string|max:20',
            'code_pattern' => 'nullable|string|max:100',
            'default_access_level' => 'nullable|string|max:20',
            'requires_versioning' => 'boolean',
            'requires_approval' => 'boolean',
            'requires_signature' => 'boolean',
            'max_file_size' => 'nullable|integer',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);
    }
}
