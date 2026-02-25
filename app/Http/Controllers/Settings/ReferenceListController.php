<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ReferenceList;
use App\Models\ReferenceValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferenceListController extends Controller
{
    public function index()
    {
        $lists = ReferenceList::withCount('values')
            ->with(['creator', 'updater'])
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
        $referenceList->load(['values' => function ($query) {
            $query->orderBy('sort_order')->orderBy('value');
        }, 'creator', 'updater']);

        return view('settings.reference-lists.show', compact('referenceList'));
    }

    public function edit(ReferenceList $referenceList)
    {
        return view('settings.reference-lists.edit', compact('referenceList'));
    }

    public function update(Request $request, ReferenceList $referenceList)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:reference_lists,name,' . $referenceList->id,
            'code' => 'required|string|max:50|unique:reference_lists,code,' . $referenceList->id,
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['updated_by'] = Auth::id();

        $referenceList->update($validated);

        return redirect()->route('settings.reference-lists.show', $referenceList)
            ->with('success', 'Liste de référence mise à jour avec succès.');
    }

    public function destroy(ReferenceList $referenceList)
    {
        try {
            $referenceList->delete();
            return redirect()->route('settings.reference-lists.index')
                ->with('success', 'Liste de référence supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('settings.reference-lists.index')
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    public function addValue(Request $request, ReferenceList $referenceList)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:190',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
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
        try {
            $value->delete();
            return redirect()->route('settings.reference-lists.show', $referenceList)
                ->with('success', 'Valeur supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('settings.reference-lists.show', $referenceList)
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
}
