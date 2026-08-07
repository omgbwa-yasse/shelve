<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\RecordType;
use App\Models\RecordTypeMetadataProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Attache/configure des `MetadataDefinition` (système ou personnalisées) sur un
 * `RecordType` donné — mandatory/visible/ordre, groupement en onglets, sécurité
 * par rôle, et propriétés de la définition (sortable, surlignage, autocomplétion,
 * unicité, masque de saisie, longueur max, copie parent, calcul).
 */
class RecordTypeMetadataProfileController extends Controller
{
    public function store(Request $request, RecordType $recordType)
    {
        $validated = $request->validate([
            'metadata_definition_id' => 'required|exists:metadata_definitions,id|unique:record_type_metadata_profiles,metadata_definition_id,NULL,id,record_type_id,'.$recordType->id,
            'mandatory' => 'boolean',
            'visible' => 'boolean',
            'readonly' => 'boolean',
            'default_value' => 'nullable|string',
            'group' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);

        $recordType->metadataProfiles()->create([
            'metadata_definition_id' => $validated['metadata_definition_id'],
            'mandatory' => $validated['mandatory'] ?? false,
            'visible' => $validated['visible'] ?? true,
            'readonly' => $validated['readonly'] ?? false,
            'default_value' => $validated['default_value'] ?? null,
            'group' => $validated['group'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('settings.record-types.edit', $recordType)
            ->with('success', 'Métadonnée attachée à la typologie.');
    }

    public function update(Request $request, RecordType $recordType, RecordTypeMetadataProfile $profile)
    {
        abort_if($profile->record_type_id !== $recordType->id, 404);

        $validated = $request->validate([
            'mandatory' => 'boolean',
            'visible' => 'boolean',
            'readonly' => 'boolean',
            'default_value' => 'nullable|string',
            'group' => 'nullable|string|max:100',
            'restricted_to_roles' => 'nullable|array',
            'restricted_to_roles.*' => 'string',
            'sort_order' => 'nullable|integer',
            // Propriétés de la définition (étape 3/4)
            'sortable' => 'boolean',
            'highlightable' => 'boolean',
            'autocomplete' => 'boolean',
            'unique' => 'boolean',
            'input_mask' => 'nullable|string|max:100',
            'max_length' => 'nullable|integer|min:1',
            'copy_source_type' => 'nullable|in:parent',
            'copy_source_field' => 'nullable|string|max:100',
            'computed_template' => 'nullable|string|max:255',
        ]);

        $profile->update([
            'mandatory' => $validated['mandatory'] ?? false,
            'visible' => $validated['visible'] ?? false,
            'readonly' => $validated['readonly'] ?? false,
            'default_value' => $validated['default_value'] ?? null,
            'group' => $validated['group'] ?? null,
            'restricted_to_roles' => $validated['restricted_to_roles'] ?? [],
            'sort_order' => $validated['sort_order'] ?? $profile->sort_order,
            'updated_by' => Auth::id(),
        ]);

        $definition = $profile->metadataDefinition;

        if ($definition) {
            $definition->update([
                'sortable' => $validated['sortable'] ?? false,
                'highlightable' => $validated['highlightable'] ?? false,
                'autocomplete' => $validated['autocomplete'] ?? false,
                'unique' => $validated['unique'] ?? false,
                'input_mask' => $validated['input_mask'] ?? null,
                'max_length' => $validated['max_length'] ?? null,
                'copy_source_type' => $validated['copy_source_type'] ?? null,
                'copy_source_field' => $validated['copy_source_field'] ?? null,
                'computed_template' => $validated['computed_template'] ?? null,
                'updated_by' => Auth::id(),
            ]);
        }

        return redirect()->route('settings.record-types.edit', $recordType)
            ->with('success', 'Profil de métadonnée mis à jour.');
    }

    public function destroy(RecordType $recordType, RecordTypeMetadataProfile $profile)
    {
        abort_if($profile->record_type_id !== $recordType->id, 404);

        $profile->delete();

        return redirect()->route('settings.record-types.edit', $recordType)
            ->with('success', 'Métadonnée détachée de la typologie.');
    }
}
