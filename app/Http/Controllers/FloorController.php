<?php


namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index()
    {
        $floors = Floor::with('building')->get();
        return view('buildings.floors.index', compact('floors'));
    }

    public function create(Building $building)
    {
        $buildings = Building::all();
        return view('buildings.floors.create', compact('building'));
    }

    public function store(Request $request, Building $building)
    {
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        Floor::create([
            'name' => $request->name,
            'description' => $request->description,
            'building_id' => $building->id,
            'creator_id' => auth()->id(),
        ]);

        return redirect()->route('buildings.show', $building)->with('success', 'Floor created successfully.');
    }

    public function show(Building $building, Floor $floor )
    {
        return view('buildings.floors.show', compact('building','floor'));
    }

    public function edit(Floor $floor)
    {
        $buildings = Building::all();
        return view('buildings.floors.edit', compact('floor', 'buildings'));
    }

    public function update(Request $request, Floor $floor)
    {
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
            'building_id' => 'required|exists:buildings,id',
        ]);

        $floor->update([
            'name' => $request->name,
            'description' => $request->description,
            'building_id' => $request->building_id,
        ]);

        return redirect()->route('floors.index')->with('success', 'Floor updated successfully.');
    }

    public function destroy(Floor $floor)
    {
        $floor->delete();
        return redirect()->route('floors.index')->with('success', 'Floor deleted successfully.');
    }
}
