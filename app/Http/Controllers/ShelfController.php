<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Shelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShelfController extends Controller
{
    private const ACCESS_DENIED_MESSAGE = 'Access denied to this shelf.';

    public function index()
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        $shelves = Shelf::with('room')
            ->whereHas('room.organisations', function($query) use ($currentOrganisationId) {
                $query->where('organisation_id', $currentOrganisationId);
            })
            ->get();

        return view('shelves.index', compact('shelves'));
    }

    public function create()
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        $rooms = Room::with('floor')
            ->whereHas('organisations', function($query) use ($currentOrganisationId) {
                $query->where('organisation_id', $currentOrganisationId);
            })
            ->get();

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
            'creator_id' => Auth::id(),
        ]);

        return redirect()->route('shelves.index')->with('success', 'Shelf created successfully.');
    }

    public function show(Shelf $shelf)
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Vérifier que la shelf appartient à une room de l'organisation courante
        if (!$shelf->room || !$shelf->room->organisations()->where('organisation_id', $currentOrganisationId)->exists()) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

        return view('shelves.show', compact('shelf'));
    }

    public function edit(Shelf $shelf)
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Vérifier que la shelf appartient à une room de l'organisation courante
        if (!$shelf->room || !$shelf->room->organisations()->where('organisation_id', $currentOrganisationId)->exists()) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

        $rooms = Room::whereHas('organisations', function($query) use ($currentOrganisationId) {
            $query->where('organisation_id', $currentOrganisationId);
        })->get();

        return view('shelves.edit', compact('shelf', 'rooms'));
    }

    public function update(Request $request, Shelf $shelf)
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Vérifier que la shelf appartient à une room de l'organisation courante
        if (!$shelf->room || !$shelf->room->organisations()->where('organisation_id', $currentOrganisationId)->exists()) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

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
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Vérifier que la shelf appartient à une room de l'organisation courante
        if (!$shelf->room || !$shelf->room->organisations()->where('organisation_id', $currentOrganisationId)->exists()) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

        $shelf->delete();
        return redirect()->route('shelves.index')->with('success', 'Shelf deleted successfully.');
    }
}
