<?php

namespace App\Http\Controllers\Settings;

use App\Exports\ReferenceValueExport;
use App\Http\Controllers\Controller;
use App\Imports\ReferenceValueImport;
use App\Models\RecordType;
use App\Models\ReferenceList;
use App\Models\ReferenceValue;
use App\Services\ReferenceValueUsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReferenceListController extends Controller
{
    public function index()
    {
        $lists = ReferenceList::withCount('values')
            ->with(['creator', 'updater'])
            ->withTrashed()
            ->orderBy('deleted_at')
            ->orderBy('name')
            ->paginate(20);

        return view('settings.reference-lists.index', compact('lists'));
    }

    public function create()
    {
        return view('settings.reference-lists.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:reference_lists,name',
            'code' => 'required|string|max:50|unique:reference_lists,code',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();

        $list = ReferenceList::create($validated);

        return redirect()->route('settings.reference-lists.show', $list)
            ->with('success', 'Liste de référence créée avec succès.');
    }

    public function show(ReferenceList $referenceList)
    {
        $referenceList->load([
            'activeValues',
            'values' => fn ($query) => $query->where('active', false)->orderBy('sort_order')->orderBy('value'),
            'creator',
            'updater',
            'linkedSchema',
        ]);

        $trashedValues = $referenceList->values()->onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return view('settings.reference-lists.show', compact('referenceList', 'trashedValues'));
    }

    public function edit(ReferenceList $referenceList)
    {
        return view('settings.reference-lists.edit', [
            'referenceList' => $referenceList,
            'linkedSchemas' => RecordType::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ReferenceList $referenceList)
    {
        $rules = [
            'name' => 'required|string|max:100|unique:reference_lists,name,'.$referenceList->id,
            'code' => 'required|string|max:50|unique:reference_lists,code,'.$referenceList->id,
            'description' => 'nullable|string',
            'active' => 'boolean',
        ];

        if ($referenceList->isLinkedSchemaEligible()) {
            $rules['linked_schema_id'] = 'nullable|exists:record_types,id';
        }

        $validated = $request->validate($rules);

        $validated['updated_by'] = Auth::id();

        $referenceList->update($validated);

        return redirect()->route('settings.reference-lists.show', $referenceList)
            ->with('success', 'Liste de référence mise à jour avec succès.');
    }

    public function destroy(ReferenceList $referenceList)
    {
        if ($referenceList->metadataDefinitions()->count() > 0) {
            return redirect()->route('settings.reference-lists.index')
                ->with('error', 'Cette liste est utilisée par '.$referenceList->metadataDefinitions()->count()
                    .' métadonnée(s) : suppression impossible. Désassociez d\'abord la liste.');
        }

        try {
            $referenceList->delete();

            return redirect()->route('settings.reference-lists.index')
                ->with('success', 'Liste de référence supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('settings.reference-lists.index')
                ->with('error', 'Erreur lors de la suppression: '.$e->getMessage());
        }
    }

    public function restore($id)
    {
        $referenceList = ReferenceList::withTrashed()->findOrFail($id);

        $referenceList->restore();

        return redirect()->route('settings.reference-lists.show', $referenceList)
            ->with('success', 'Liste de référence restaurée.');
    }

    public function addValue(Request $request, ReferenceList $referenceList)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:190',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'extra_attributes' => 'nullable|array',
            'active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Verify code uniqueness within the list
        $exists = ReferenceValue::where('list_id', $referenceList->id)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà dans cette liste.']);
        }

        $validated['list_id'] = $referenceList->id;
        $validated['created_by'] = Auth::id();

        ReferenceValue::create($validated);

        return redirect()->route('settings.reference-lists.show', $referenceList)
            ->with('success', 'Valeur ajoutée avec succès.');
    }

    public function updateValue(Request $request, ReferenceList $referenceList, ReferenceValue $value)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:190',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'extra_attributes' => 'nullable|array',
            'active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Verify code uniqueness within the list (excluding current value)
        $exists = ReferenceValue::where('list_id', $referenceList->id)
            ->where('code', $validated['code'])
            ->where('id', '!=', $value->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['code' => 'Ce code existe déjà dans cette liste.']);
        }

        $validated['updated_by'] = Auth::id();

        $value->update($validated);

        return redirect()->route('settings.reference-lists.show', $referenceList)
            ->with('success', 'Valeur mise à jour avec succès.');
    }

    public function deleteValue(ReferenceList $referenceList, ReferenceValue $value)
    {
        $usage = app(ReferenceValueUsageService::class);

        if ($usage->isValueUsed($value)) {
            return redirect()->route('settings.reference-lists.show', $referenceList)
                ->with('error', 'Cette valeur est utilisée par '.$usage->usageCount($value)
                    .' notice(s) : suppression refusée. Désactivez-la ou remplacez-la d\'abord.');
        }

        try {
            $value->delete();

            return redirect()->route('settings.reference-lists.show', $referenceList)
                ->with('success', 'Valeur supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('settings.reference-lists.show', $referenceList)
                ->with('error', 'Erreur lors de la suppression: '.$e->getMessage());
        }
    }

    public function restoreValue(ReferenceList $referenceList, $id)
    {
        $value = $referenceList->values()->withTrashed()->findOrFail($id);

        $value->restore();

        return redirect()->route('settings.reference-lists.show', $referenceList)
            ->with('success', 'Valeur restaurée.');
    }

    /**
     * Action « Supprimer les désactivés non utilisés ».
     */
    public function purgeInactive(ReferenceList $referenceList)
    {
        $deleted = app(ReferenceValueUsageService::class)->deleteUnusedInactiveValues($referenceList);

        return redirect()->route('settings.reference-lists.show', $referenceList)
            ->with($deleted > 0 ? 'success' : 'info',
                $deleted > 0 ? $deleted.' valeur(s) désactivée(s) non utilisée(s) supprimée(s).' : 'Aucune valeur désactivée à purger.');
    }

    /*
    |--------------------------------------------------------------------------
    | Étape 7 — Import / Export en masse des valeurs d'un domaine
    |--------------------------------------------------------------------------
    */
    public function importValues(Request $request, ReferenceList $referenceList)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $import = new ReferenceValueImport($referenceList, Auth::id());
        Excel::import($import, $request->file('file'));

        $summary = $import->getSummary();

        $message = $summary['created'].' valeur(s) créée(s), '.$summary['updated'].' mise(s) à jour, '
            .count($summary['errors']).' ligne(s) en erreur.';

        if (count($summary['errors']) > 0) {
            return redirect()->route('settings.reference-lists.show', $referenceList)
                ->with('import_errors', $summary['errors'])
                ->with('success', $message);
        }

        return redirect()->route('settings.reference-lists.show', $referenceList)
            ->with('success', $message);
    }

    public function exportValues(ReferenceList $referenceList)
    {
        return Excel::download(new ReferenceValueExport($referenceList), 'domaine-'.$referenceList->code.'.xlsx');
    }
}
