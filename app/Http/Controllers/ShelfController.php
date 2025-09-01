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

        $shelves = Shelf::with(['room.floor.building', 'containers.status', 'containers.property'])
            ->whereHas('room.organisations', function($query) use ($currentOrganisationId) {
                $query->where('organisation_id', $currentOrganisationId);
            })
            ->get();

        // Calculate statistics for each shelf
        $shelves->each(function($shelf) {
            $shelf->total_capacity = $shelf->face * $shelf->ear * $shelf->shelf;
            $shelf->occupied_spots = $shelf->containers->count();
            $shelf->available_spots = max(0, $shelf->total_capacity - $shelf->occupied_spots);
            $shelf->occupancy_percentage = $shelf->total_capacity > 0 ? round(($shelf->occupied_spots / $shelf->total_capacity) * 100, 1) : 0;
            $shelf->volumetry_ml = ($shelf->face * $shelf->ear * $shelf->shelf * $shelf->shelf_length) / 100;
        });

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

        // Load shelf with all related data
        $shelf = Shelf::with([
            'room.floor.building',
            'containers.status',
            'containers.property',
            'containers' => function($query) use ($currentOrganisationId) {
                $query->where('creator_organisation_id', $currentOrganisationId)
                      ->orderBy('created_at', 'desc');
            }
        ])->find($shelf->id);

        // Calculate shelf statistics
        $shelf->total_capacity = $shelf->face * $shelf->ear * $shelf->shelf;
        $shelf->occupied_spots = $shelf->containers->count();
        $shelf->available_spots = max(0, $shelf->total_capacity - $shelf->occupied_spots);
        $shelf->occupancy_percentage = $shelf->total_capacity > 0 ? round(($shelf->occupied_spots / $shelf->total_capacity) * 100, 1) : 0;
        $shelf->volumetry_ml = ($shelf->face * $shelf->ear * $shelf->shelf * $shelf->shelf_length) / 100;

        // Create 3D grid for visualization
        $shelfGrid = [];
        for ($face = 1; $face <= $shelf->face; $face++) {
            for ($ear = 1; $ear <= $shelf->ear; $ear++) {
                for ($shelfLevel = 1; $shelfLevel <= $shelf->shelf; $shelfLevel++) {
                    $shelfGrid[$face][$ear][$shelfLevel] = null; // Empty by default
                }
            }
        }

        // Map containers to grid positions (simplified - containers fill positions sequentially)
        $containerIndex = 0;
        foreach ($shelf->containers as $container) {
            if ($containerIndex < $shelf->total_capacity) {
                $face = floor($containerIndex / ($shelf->ear * $shelf->shelf)) + 1;
                $ear = floor(($containerIndex % ($shelf->ear * $shelf->shelf)) / $shelf->shelf) + 1;
                $shelfLevel = ($containerIndex % $shelf->shelf) + 1;
                
                $shelfGrid[$face][$ear][$shelfLevel] = $container;
            }
            $containerIndex++;
        }

        return view('shelves.show', compact('shelf', 'shelfGrid'));
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
