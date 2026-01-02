<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalFolderType;
use App\Models\RecordDigitalFolderMetadataProfile;
use App\Models\MetadataDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderTypeMetadataProfileController extends Controller
{
    public function index(RecordDigitalFolderType $folderType)
    {
        $folderType->load([
            'metadataProfiles.metadataDefinition',
            'metadataProfiles' => function ($query) {
                $query->ordered();
            }
        ]);

        $availableDefinitions = MetadataDefinition::active()
            ->whereNotIn('id', $folderType->metadataProfiles->pluck('metadata_definition_id'))
            ->ordered()
            ->get();

        return view('settings.folder-types.metadata-profiles.index', compact('folderType', 'availableDefinitions'));
    }

    public function store(Request $request, RecordDigitalFolderType $folderType)
    {
        $validated = $request->validate([
            'metadata_definition_id' => 'required|exists:metadata_definitions,id',
            'mandatory' => 'boolean',
            'visible' => 'boolean',
            'readonly' => 'boolean',
            'default_value' => 'nullable|string',
            'validation_rules' => 'nullable|json',
            'sort_order' => 'nullable|integer',
        ]);

        // Check if profile already exists
        $exists = RecordDigitalFolderMetadataProfile::where('folder_type_id', $folderType->id)
            ->where('metadata_definition_id', $validated['metadata_definition_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['metadata_definition_id' => 'Cette métadonnée est déjà associée à ce type de dossier.']);
        }

        $validated['folder_type_id'] = $folderType->id;
        $validated['created_by'] = Auth::id();

        RecordDigitalFolderMetadataProfile::create($validated);

        return redirect()->route('settings.folder-types.metadata-profiles.index', $folderType)
            ->with('success', 'Métadonnée ajoutée au profil avec succès.');
    }

    public function update(Request $request, RecordDigitalFolderType $folderType, RecordDigitalFolderMetadataProfile $profile)
    {
        $validated = $request->validate([
            'mandatory' => 'boolean',
            'visible' => 'boolean',
            'readonly' => 'boolean',
            'default_value' => 'nullable|string',
            'validation_rules' => 'nullable|json',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['updated_by'] = Auth::id();

        $profile->update($validated);

        return redirect()->route('settings.folder-types.metadata-profiles.index', $folderType)
            ->with('success', 'Profil de métadonnée mis à jour avec succès.');
    }

    public function destroy(RecordDigitalFolderType $folderType, RecordDigitalFolderMetadataProfile $profile)
    {
        try {
            $profile->delete();
            return redirect()->route('settings.folder-types.metadata-profiles.index', $folderType)
                ->with('success', 'Métadonnée retirée du profil avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('settings.folder-types.metadata-profiles.index', $folderType)
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function bulkUpdate(Request $request, RecordDigitalFolderType $folderType)
    {
        $validated = $request->validate([
            'profiles' => 'required|array',
            'profiles.*.id' => 'required|exists:record_digital_folder_metadata_profiles,id',
            'profiles.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['profiles'] as $profileData) {
            RecordDigitalFolderMetadataProfile::where('id', $profileData['id'])
                ->update([
                    'sort_order' => $profileData['sort_order'],
                    'updated_by' => Auth::id(),
                ]);
        }

        return redirect()->route('settings.folder-types.metadata-profiles.index', $folderType)
            ->with('success', 'Ordre des métadonnées mis à jour avec succès.');
    }
}
