<?php

namespace App\Http\Controllers\Museum;

use App\Http\Controllers\Controller;
use App\Models\RecordArtifact;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * Display a listing of collections.
     */
    public function index(Request $request)
    {
        // Group artifacts by collection
        $query = RecordArtifact::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('collection', 'like', "%{$search}%");
            });
        }

        // Filter by collection
        if ($request->filled('collection')) {
            $query->where('collection', $request->collection);
        }

        // Get collections with statistics
        $collections = RecordArtifact::selectRaw('collection, COUNT(*) as pieces_count')
            ->groupBy('collection')
            ->get();

        $artifacts = $query->orderBy('collection')->orderBy('code')->paginate(20);

        return view('museum.collections.index', compact('collections', 'artifacts'));
    }

    /**
     * Show the form for creating a new collection.
     */
    public function create()
    {
        // TODO: Implémenter le formulaire de création
        return view('museum.collections.create');
    }

    /**
     * Store a newly created collection in storage.
     */
    public function store(Request $request)
    {
        // TODO: Implémenter la logique de création
        return redirect()->route('museum.collections.index')
            ->with('success', 'Collection créée avec succès.');
    }

    /**
     * Display the specified collection.
     */
    public function show($id)
    {
        // TODO: Implémenter la logique d'affichage
        return view('museum.collections.show');
    }

    /**
     * Show the form for editing the specified collection.
     */
    public function edit($id)
    {
        // TODO: Implémenter le formulaire d'édition
        return view('museum.collections.edit');
    }

    /**
     * Update the specified collection in storage.
     */
    public function update(Request $request, $id)
    {
        // TODO: Implémenter la logique de mise à jour
        return redirect()->route('museum.collections.index')
            ->with('success', 'Collection modifiée avec succès.');
    }

    /**
     * Remove the specified collection from storage.
     */
    public function destroy($id)
    {
        // TODO: Implémenter la logique de suppression
        return redirect()->route('museum.collections.index')
            ->with('success', 'Collection supprimée avec succès.');
    }
}
