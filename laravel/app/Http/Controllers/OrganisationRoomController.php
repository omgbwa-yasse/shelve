<?php

namespace App\Http\Controllers;
use App\Models\Organisation;
use App\Models\Room;
use Illuminate\Http\Request;

class OrganisationRoomController extends Controller
{
    public function index(Organisation $organisation)
    {
        $rooms = $organisation->rooms;
        return view('organisations.rooms.index', compact('organisation', 'rooms'));
    }



    public function create(Organisation $organisation)
    {
        $rooms = Room::all();
        return view('organisations.rooms.create', compact('organisation', 'rooms'));
    }




    public function store(Request $request, Organisation $organisation)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
        ]);

        $organisation->rooms()->attach($request->room_id);

        return redirect()->route('organisations.rooms.index', $organisation)->with('success', 'Room added successfully.');
    }



    public function destroy(Organisation $organisation, Room $room)
    {
        $organisation->rooms()->detach($room->id);

        return redirect()->route('organisations.rooms.index', $organisation)->with('success', 'Room removed successfully.');
    }


}


