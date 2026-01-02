<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalDocumentMetadataProfile;
use App\Models\MetadataDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTypeMetadataProfileController extends Controller
{
    public function index(RecordDigitalDocumentType $documentType)
    {
        $documentType->load([
            'metadataProfiles.metadataDefinition',
            'metadataProfiles' => function ($query) {
                $query->ordered();
            }
        ]);

        $availableDefinitions = MetadataDefinition::active()
            ->whereNotIn('id', $documentType->metadataProfiles->pluck('metadata_definition_id'))
            ->ordered()
            ->get();

        return view('settings.document-types.metadata-profiles.index', compact('documentType', 'availableDefinitions'));
    }

    public function store(Request $request, RecordDigitalDocumentType $documentType)
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
        $exists = RecordDigitalDocumentMetadataProfile::where('document_type_id', $documentType->id)
            ->where('metadata_definition_id', $validated['metadata_definition_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['metadata_definition_id' => 'Cette métadonnée est déjà associée à ce type de document.']);
        }

        $validated['document_type_id'] = $documentType->id;
        $validated['created_by'] = Auth::id();

        RecordDigitalDocumentMetadataProfile::create($validated);

        return redirect()->route('settings.document-types.metadata-profiles.index', $documentType)
            ->with('success', 'Métadonnée ajoutée au profil avec succès.');
    }

    public function update(Request $request, RecordDigitalDocumentType $documentType, RecordDigitalDocumentMetadataProfile $profile)
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

        return redirect()->route('settings.document-types.metadata-profiles.index', $documentType)
            ->with('success', 'Profil de métadonnée mis à jour avec succès.');
    }

    public function destroy(RecordDigitalDocumentType $documentType, RecordDigitalDocumentMetadataProfile $profile)
    {
        try {
            $profile->delete();
            return redirect()->route('settings.document-types.metadata-profiles.index', $documentType)
                ->with('success', 'Métadonnée retirée du profil avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('settings.document-types.metadata-profiles.index', $documentType)
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function bulkUpdate(Request $request, RecordDigitalDocumentType $documentType)
    {
        $validated = $request->validate([
            'profiles' => 'required|array',
            'profiles.*.id' => 'required|exists:record_digital_document_metadata_profiles,id',
            'profiles.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['profiles'] as $profileData) {
            RecordDigitalDocumentMetadataProfile::where('id', $profileData['id'])
                ->update([
                    'sort_order' => $profileData['sort_order'],
                    'updated_by' => Auth::id(),
                ]);
        }

        return redirect()->route('settings.document-types.metadata-profiles.index', $documentType)
            ->with('success', 'Ordre des métadonnées mis à jour avec succès.');
    }
}
