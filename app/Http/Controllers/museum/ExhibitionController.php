<?php

namespace App\Http\Controllers\Museum;

use App\Http\Controllers\Controller;
use App\Models\RecordArtifactExhibition;
use App\Models\RecordArtifact;
use Illuminate\Http\Request;

class ExhibitionController extends Controller
{
    /**
     * Display a listing of exhibitions.
     */
    public function index(Request $request)
    {
        $query = RecordArtifactExhibition::with('artifact');

        // Filter by status
        $status = $request->get('status', 'current');

        if ($status === 'current') {
            $query->where('start_date', '<=', now())
                  ->where('end_date', '>=', now());
        } elseif ($status === 'upcoming') {
            $query->where('start_date', '>', now());
        } elseif ($status === 'past') {
            $query->where('end_date', '<', now());
        }

        $exhibitions = $query->orderBy('start_date', 'desc')->paginate(20);

        return view('museum.exhibitions.index', compact('exhibitions', 'status'));
    }

    /**
     * Show the form for creating a new exhibition.
     */
    public function create()
    {
        // TODO: Implémenter le formulaire de création
        return view('museum.exhibitions.create');
    }

    /**
     * Store a newly created exhibition in storage.
     */
    public function store(Request $request)
    {
        // TODO: Implémenter la logique de création
        return redirect()->route('museum.exhibitions.index')
            ->with('success', 'Exposition créée avec succès.');
    }

    /**
     * Display the specified exhibition.
     */
    public function show($id)
    {
        // TODO: Implémenter la logique d'affichage
        return view('museum.exhibitions.show');
    }

    /**
     * Show the form for editing the specified exhibition.
     */
    public function edit($id)
    {
        // TODO: Implémenter le formulaire d'édition
        return view('museum.exhibitions.edit');
    }

    /**
     * Update the specified exhibition in storage.
     */
    public function update(Request $request, $id)
    {
        // TODO: Implémenter la logique de mise à jour
        return redirect()->route('museum.exhibitions.index')
            ->with('success', 'Exposition modifiée avec succès.');
    }

    /**
     * Remove the specified exhibition from storage.
     */
    public function destroy($id)
    {
        // TODO: Implémenter la logique de suppression
        return redirect()->route('museum.exhibitions.index')
            ->with('success', 'Exposition supprimée avec succès.');
    }
}
