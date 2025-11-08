<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        // TODO: Implémenter la logique de liste des catégories
        return view('library.categories.index');
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        // TODO: Implémenter le formulaire de création
        return view('library.categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        // TODO: Implémenter la logique de création
        return redirect()->route('library.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit($id)
    {
        // TODO: Implémenter le formulaire d'édition
        return view('library.categories.edit');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        // TODO: Implémenter la logique de mise à jour
        return redirect()->route('library.categories.index')
            ->with('success', 'Catégorie modifiée avec succès.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        // TODO: Implémenter la logique de suppression
        return redirect()->route('library.categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }
}
