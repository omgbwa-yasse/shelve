<?php

namespace App\Http\Controllers;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{

    public function index()
    {
        $buildings = Building::all();
        return view('buildings.index', compact('buildings'));
    }

    public function create()
    {
        return view('buildings.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        Building::create([
            'name' => $request->name,
            'description' => $request->description,
            'creator_id' => auth()->id(),
        ]);

        return redirect()->route('buildings.index')->with('success', 'Building created successfully.');
    }

    public function show(Building $building)
    {
        $building->load('floors');
        return view('buildings.show', compact('building'));
    }

    public function edit(Building $building)
    {
        return view('buildings.edit', compact('building'));
    }

    public function update(Request $request, Building $building)
    {
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        $building->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('buildings.index')->with('success', 'Building updated successfully.');
    }

    public function destroy(Building $building)
    {
        $building->delete();
        return redirect()->route('buildings.index')->with('success', 'Building deleted successfully.');
    }


}
