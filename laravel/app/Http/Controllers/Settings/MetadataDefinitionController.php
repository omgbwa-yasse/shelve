<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\MetadataDefinition;
use App\Models\ReferenceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MetadataDefinitionController extends Controller
{
    public function index()
    {
        $definitions = MetadataDefinition::with(['referenceList', 'creator', 'updater'])
            ->ordered()
            ->paginate(20);

        return view('settings.metadata-definitions.index', compact('definitions'));
    }

    public function create()
    {
        $referenceLists = ReferenceList::active()->get();
        $dataTypes = [
            'text' => 'Texte',
            'textarea' => 'Zone de texte',
            'number' => 'Nombre',
            'date' => 'Date',
            'datetime' => 'Date et heure',
            'boolean' => 'Oui/Non',
            'select' => 'Liste déroulante',
            'multi_select' => 'Sélection multiple',
            'reference_list' => 'Liste de référence',
            'email' => 'Email',
            'url' => 'URL',
        ];

        return view('settings.metadata-definitions.create', compact('referenceLists', 'dataTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:metadata_definitions,code',
            'description' => 'nullable|string',
            'data_type' => 'required|in:text,textarea,number,date,datetime,boolean,select,multi_select,reference_list,email,url',
            'validation_rules' => 'nullable|json',
            'options' => 'nullable|json',
            'reference_list_id' => 'nullable|exists:reference_lists,id',
            'searchable' => 'boolean',
            'active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['created_by'] = Auth::id();

        $definition = MetadataDefinition::create($validated);

        return redirect()->route('settings.metadata-definitions.index')
            ->with('success', 'Définition de métadonnée créée avec succès.');
    }

    public function edit(MetadataDefinition $metadataDefinition)
    {
        $referenceLists = ReferenceList::active()->get();
        $dataTypes = [
            'text' => 'Texte',
            'textarea' => 'Zone de texte',
            'number' => 'Nombre',
            'date' => 'Date',
            'datetime' => 'Date et heure',
            'boolean' => 'Oui/Non',
            'select' => 'Liste déroulante',
            'multi_select' => 'Sélection multiple',
            'reference_list' => 'Liste de référence',
            'email' => 'Email',
            'url' => 'URL',
        ];

        return view('settings.metadata-definitions.edit', compact('metadataDefinition', 'referenceLists', 'dataTypes'));
    }

    public function update(Request $request, MetadataDefinition $metadataDefinition)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:metadata_definitions,code,' . $metadataDefinition->id,
            'description' => 'nullable|string',
            'data_type' => 'required|in:text,textarea,number,date,datetime,boolean,select,multi_select,reference_list,email,url',
            'validation_rules' => 'nullable|json',
            'options' => 'nullable|json',
            'reference_list_id' => 'nullable|exists:reference_lists,id',
            'searchable' => 'boolean',
            'active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['updated_by'] = Auth::id();

        $metadataDefinition->update($validated);

        return redirect()->route('settings.metadata-definitions.index')
            ->with('success', 'Définition de métadonnée mise à jour avec succès.');
    }

    public function destroy(MetadataDefinition $metadataDefinition)
    {
        try {
            $metadataDefinition->delete();
            return redirect()->route('settings.metadata-definitions.index')
                ->with('success', 'Définition de métadonnée supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('settings.metadata-definitions.index')
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
}
