<?php


namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    public function index()
    {
        return redirect()->route('buildings.index');
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

    public function edit(Building $building, Floor $floor)
    {
        $buildings = Building::all();
        return view('buildings.floors.edit', compact('building', 'floor', 'buildings'));
    }

    public function update(Request $request, Building $building, Floor $floor)
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

        return redirect()->route('buildings.show', $building)->with('success', 'Floor updated successfully.');
    }

    public function destroy(Building $building, Floor $floor)
    {
        $floor->delete();
        return redirect()->route('buildings.show', $building)->with('success', 'Floor deleted successfully.');
    }
}
