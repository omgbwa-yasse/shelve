<?php

namespace App\Http\Controllers;
use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{

    public function index()
    {
        $buildings = Building::paginate(25);
        return view('buildings.index', compact('buildings'));
    }

    public function create()
    {
        $visibilityOptions = [
            'public' => 'Public',
            'private' => 'Privé',
            'inherit' => 'Hériter'
        ];
        return view('buildings.create', compact('visibilityOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
            'address' => 'nullable|max:255',
            'capacity' => 'nullable|integer|min:0',
            'visibility' => 'required|in:public,private,inherit',
        ]);

        Building::create([
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'capacity' => $request->capacity,
            'visibility' => $request->visibility,
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
        $visibilityOptions = [
            'public' => 'Public',
            'private' => 'Privé',
            'inherit' => 'Hériter'
        ];
        return view('buildings.edit', compact('building', 'visibilityOptions'));
    }

    public function update(Request $request, Building $building)
    {
        $request->validate([
            'name' => 'required|max:100',
            'description' => 'nullable',
            'address' => 'nullable|max:255',
            'capacity' => 'nullable|integer|min:0',
            'visibility' => 'required|in:public,private,inherit',
        ]);

        $building->update([
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'capacity' => $request->capacity,
            'visibility' => $request->visibility,
        ]);

        return redirect()->route('buildings.index')->with('success', 'Building updated successfully.');
    }

    public function destroy(Building $building)
    {
        $building->delete();
        return redirect()->route('buildings.index')->with('success', 'Building deleted successfully.');
    }
}
