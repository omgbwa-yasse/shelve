<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Shelf;
use Illuminate\Http\Request;

class ShelfController extends Controller
{
    public function index()
    {
        $shelves = Shelf::with('room')->get();
        return view('shelves.index', compact('shelves'));
    }

    public function create()
    {
        $rooms = Room::with('floor')->get();
        return view('shelves.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:30',
            'observation' => 'nullable',
            'face' => 'required|numeric|max:10',
            'ear' => 'required|numeric|max:10',
            'shelf' => 'required|numeric|max:10',
            'shelf_length' => 'required|numeric',
            'room_id' => 'required|exists:rooms,id',
        ]);

        Shelf::create([
            'code' => $request->code,
            'observation' => $request->observation,
            'face' => $request->face,
            'ear' => $request->ear,
            'shelf' => $request->shelf,
            'shelf_length' => $request->shelf_length,
            'room_id' => $request->room_id,
            'creator_id' => auth()->id(),
        ]);

        return redirect()->route('shelves.index')->with('success', 'Shelf created successfully.');
    }

    public function show(Shelf $shelf)
    {
        return view('shelves.show', compact('shelf'));
    }

    public function edit(Shelf $shelf)
    {
        $rooms = Room::all();
        return view('shelves.edit', compact('shelf', 'rooms'));
    }

    public function update(Request $request, Shelf $shelf)
    {
        $request->validate([
            'code' => 'required|max:30',
            'observation' => 'nullable',
            'face' => 'required|max:10',
            'ear' => 'required|max:10',
            'shelf' => 'required|max:10',
            'shelf_length' => 'required|numeric',
            'room_id' => 'required|exists:rooms,id',
        ]);

        $shelf->update([
            'code' => $request->code,
            'observation' => $request->observation,
            'face' => $request->face,
            'ear' => $request->ear,
            'shelf' => $request->shelf,
            'shelf_length' => $request->shelf_length,
            'room_id' => $request->room_id,
        ]);

        return redirect()->route('shelves.index')->with('success', 'Shelf updated successfully.');
    }

    public function destroy(Shelf $shelf)
    {
        $shelf->delete();
        return redirect()->route('shelves.index')->with('success', 'Shelf deleted successfully.');
    }
}
