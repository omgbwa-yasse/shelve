<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    private const ACCESS_DENIED_MESSAGE = 'Access denied to this room.';

    public function index()
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        $rooms = Room::with('floor')
            ->whereHas('organisations', function($query) use ($currentOrganisationId) {
                $query->where('organisation_id', $currentOrganisationId);
            })
            ->get();

        // Calculer les statistiques pour chaque room
        $rooms->each(function($room) {
            $room->shelves_count = $room->shelves()->count();
            $room->is_visible = $room->visibility === 'public';
        });

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

        $room = Room::create([
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'visibility' => $request->visibility,
            'type' => $request->type,
            'floor_id' => $request->floor_id,
            'creator_id' => Auth::id(),
        ]);

        // Attacher la room à l'organisation courante de l'utilisateur
        $room->organisations()->attach(Auth::user()->current_organisation_id);

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
            'creator_id' => Auth::id(),
        ]);

        return redirect()->route('rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Vérifier que la room appartient à l'organisation courante
        if (!$room->organisations()->where('organisation_id', $currentOrganisationId)->exists()) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully.');
    }
}
