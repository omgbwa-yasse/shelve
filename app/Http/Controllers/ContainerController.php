<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\ContainerProperty;
use App\Models\ContainerStatus;
use App\Models\Shelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContainerController extends Controller
{
    private const ACCESS_DENIED_MESSAGE = 'Access denied to this container.';

    public function index()
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        $containers = Container::with(['shelf.room.floor.building', 'status', 'property'])
            ->whereHas('shelf.room.organisations', function($query) use ($currentOrganisationId) {
                $query->where('organisation_id', $currentOrganisationId);
            })
            ->paginate(25);

        return view('containers.index', compact('containers'));
    }



    public function create()
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        $shelves = Shelf::whereHas('room.organisations', function($query) use ($currentOrganisationId) {
            $query->where('organisation_id', $currentOrganisationId);
        })->get();

        $statuses = ContainerStatus::all();
        $properties = ContainerProperty::all();
        return view('containers.create', compact('shelves', 'statuses', 'properties'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:20|unique:containers,code',
            'shelve_id' => 'required|exists:shelves,id',
            'status_id' => 'required|exists:container_statuses,id',
            'property_id' => 'required|exists:container_properties,id',
        ]);

        Container::create([
            'code' => $request->code,
            'shelve_id' => $request->shelve_id,
            'status_id' => $request->status_id,
            'property_id' => $request->property_id,
            'creator_id' => Auth::id(),
            'creator_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return redirect()->route('containers.index')->with('success', 'Container created successfully.');
    }




    public function show(Container $container)
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Vérifier que le container appartient à une shelf de l'organisation courante
        if (!$container->shelf || !$container->shelf->room || !$container->shelf->room->organisations()->where('organisation_id', $currentOrganisationId)->exists()) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

        return view('containers.show', compact('container'));
    }




    public function edit(Container $container)
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Vérifier que le container appartient à une shelf de l'organisation courante
        if (!$container->shelf || !$container->shelf->room || !$container->shelf->room->organisations()->where('organisation_id', $currentOrganisationId)->exists()) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

        $shelves = Shelf::whereHas('room.organisations', function($query) use ($currentOrganisationId) {
            $query->where('organisation_id', $currentOrganisationId);
        })->get();

        $statuses = ContainerStatus::all();
        $properties = ContainerProperty::all();
        return view('containers.edit', compact('container', 'shelves', 'statuses', 'properties'));
    }




    public function update(Request $request, Container $container)
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Vérifier que le container appartient à une shelf de l'organisation courante
        if (!$container->shelf || !$container->shelf->room || !$container->shelf->room->organisations()->where('organisation_id', $currentOrganisationId)->exists()) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

        $request->validate([
            'code' => 'required|max:20|unique:containers,code,' . $container->id,
            'shelve_id' => 'required|exists:shelves,id',
            'status_id' => 'required|exists:container_statuses,id',
            'property_id' => 'required|exists:container_properties,id',
        ]);

        $container->update([
            'code' => $request->code,
            'shelve_id' => $request->shelve_id,
            'status_id' => $request->status_id,
            'property_id' => $request->property_id,
            'creator_id' => Auth::id(),
            'creator_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return redirect()->route('containers.index')->with('success', 'Container updated successfully.');
    }




    public function destroy(Container $container)
    {
        $currentOrganisationId = Auth::user()->current_organisation_id;

        // Vérifier que le container appartient à une shelf de l'organisation courante
        if (!$container->shelf || !$container->shelf->room || !$container->shelf->room->organisations()->where('organisation_id', $currentOrganisationId)->exists()) {
            abort(403, self::ACCESS_DENIED_MESSAGE);
        }

        $container->delete();
        return redirect()->route('containers.index')->with('success', 'Container deleted successfully.');
    }
}
