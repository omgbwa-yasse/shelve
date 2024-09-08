<?php

namespace App\Http\Controllers;


use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('floor','type')->get();
        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        $floors = Floor::all();
        $types = RoomType::all();
        return view('rooms.create', compact('floors','types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'nullable',
            'floor_id' => 'required|exists:floors,id',
            'type_id' => 'required|exists:room_types,id',
        ]);

        Room::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'floor_id' => $request->floor_id,
            'creator_id' => auth()->id(),
            'type_id' => $request->type_id,
        ]);

        return redirect()->route('rooms.index')->with('success', 'Room created successfully.');
    }

    public function show(Room $room)
    {
        $room->load('floor','type');
        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $floors = Floor::all();
        $types = RoomType::all();
        return view('rooms.edit', compact('room', 'floors','types'));
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'nullable',
            'floor_id' => 'required|exists:floors,id',
            'type_id' => 'required|exists:room_types,id',
        ]);

        $room->update([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'floor_id' => $request->floor_id,
            'creator_id' => auth()->id(),
            'type_id' => $request->type_id,
        ]);

        return redirect()->route('rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully.');
    }


}
