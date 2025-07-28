<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('floor')->get();
        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        $floors = Floor::all();
        $visibilityOptions = [
            'public' => 'Public',
            'private' => 'Privé',
            'inherit' => 'Hériter du bâtiment'
        ];
        $typeOptions = [
            'archives' => 'Archives',
            'producer' => 'Producteur'
        ];
        return view('rooms.create', compact('floors', 'visibilityOptions', 'typeOptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'nullable',
            'visibility' => 'required|in:public,private,inherit',
            'type' => 'required|in:archives,producer',
            'floor_id' => 'required|exists:floors,id',
        ]);

        Room::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'visibility' => $request->visibility,
            'type' => $request->type,
            'floor_id' => $request->floor_id,
            'creator_id' => 1, // TODO: Utiliser l'ID de l'utilisateur authentifié
        ]);

        return redirect()->route('rooms.index')->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        $room->load('floor');
        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $floors = Floor::all();
        $visibilityOptions = [
            'public' => 'Public',
            'private' => 'Privé',
            'inherit' => 'Hériter du bâtiment'
        ];
        $typeOptions = [
            'archives' => 'Archives',
            'producer' => 'Producteur'
        ];
        return view('rooms.edit', compact('room', 'floors', 'visibilityOptions', 'typeOptions'));
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'nullable',
            'visibility' => 'required|in:public,private,inherit',
            'type' => 'required|in:archives,producer',
            'floor_id' => 'required|exists:floors,id',
        ]);

        $room->update([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'visibility' => $request->visibility,
            'type' => $request->type,
            'floor_id' => $request->floor_id,
            'creator_id' => 1, // TODO: Utiliser l'ID de l'utilisateur authentifié
        ]);

        return redirect()->route('rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully.');
    }
}
