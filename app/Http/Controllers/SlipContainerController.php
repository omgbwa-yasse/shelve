<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\ContainerProperty;
use App\Models\ContainerStatus;
use App\Models\Shelf;
use Illuminate\Http\Request;

class SlipContainerController extends Controller
{
    public function index()
    {
        $containers = Container::with('shelf', 'status', 'property')
            ->where('creator_organisation_id', auth()->user()->current_organisation_id)
            ->where('is_archived', false)
            ->get();

        return view('transferrings.containers.index', compact('containers'));
    }





    public function create()
    {
        $organisationId = auth()->user()->current_organisation_id;
        $shelves = Shelf::whereHas('room.organisations', function ($query) use ($organisationId) {
            $query->where('organisation_id', $organisationId);
        })->get();

        $statuses = ContainerStatus::all();
        $properties = ContainerProperty::all();
        return view('transferrings.containers.create', compact('shelves', 'statuses', 'properties'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:20|unique:containers,code',
            'description' => 'required|string|max:200',
            'shelve_id' => 'required|exists:shelves,id',
            'status_id' => 'required|exists:container_statuses,id',
            'property_id' => 'required|exists:container_properties,id',
        ]);

        Container::create([
            'code' => $request->code,
            'description' => $request->description,
            'shelve_id' => $request->shelve_id,
            'status_id' => $request->status_id,
            'property_id' => $request->property_id,
            'creator_id' => auth()->id(),
            'creator_organisation_id' => auth()->user()->current_organisation_id,
        ]);

        return redirect()->route('slips.containers.index')->with('success', 'Container created successfully.');
    }




    public function show(Container $container)
    {
        return view('transferrings.containers.show', compact('container'));
    }




    public function edit(Container $container)
    {
        $organisationId = auth()->user()->current_organisation_id;
        $shelves = Shelf::whereHas('room.organisations', function ($query) use ($organisationId) {
            $query->where('organisation_id', $organisationId);
        })->get();

        $statuses = ContainerStatus::all();
        $properties = ContainerProperty::all();
        return view('transferrings.containers.edit', compact('container', 'shelves', 'statuses', 'properties'));
    }



    public function update(Request $request, Container $container)
    {
        $request->validate([
            'code' => 'required|max:20|unique:containers,code,' . $container->id,
            'description' => 'required|string|max:200',
            'shelve_id' => 'required|exists:shelves,id',
            'status_id' => 'required|exists:container_statuses,id',
            'property_id' => 'required|exists:container_properties,id',
        ]);

        $container->update([
            'code' => $request->code,
            'description' => $request->description,
            'shelve_id' => $request->shelve_id,
            'status_id' => $request->status_id,
            'property_id' => $request->property_id,
            'creator_id' => auth()->id(),
            'creator_organisation_id' => auth()->user()->current_organisation_id,
        ]);

        return redirect()->route('slips.containers.index')->with('success', 'Container updated successfully.');
    }

    public function destroy(Container $container)
    {
        $container->delete();

        return redirect()->route('slips.containers.index')->with('success', 'Container deleted successfully.');
    }
}
