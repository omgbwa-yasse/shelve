<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\ContainerProperty;
use App\Models\ContainerStatus;
use App\Models\Shelf;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    public function index()
    {
        $containers = Container::with('shelf', 'status', 'property')->get();
        return view('containers.index', compact('containers'));
    }

    public function create()
    {
        $shelves = Shelf::all();
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
            'creator_id' => auth()->id(),
            'user_organisation_id' => auth()->user()->current8orgqnisqtion_id,
        ]);

        return redirect()->route('containers.index')->with('success', 'Container created successfully.');
    }

    public function show(Container $container)
    {
        return view('containers.show', compact('container'));
    }

    public function edit(Container $container)
    {
        $shelves = Shelf::all();
        $statuses = ContainerStatus::all();
        $properties = ContainerProperty::all();
        return view('containers.edit', compact('container', 'shelves', 'statuses', 'properties'));
    }

    public function update(Request $request, Container $container)
    {
        $request->validate([
            'code' => 'required|max:20|unique:containers,code,' . $container->id,
            'shelve_id' => 'required|exists:shelves,id',
            'status_id' => 'required|exists:container_status,id',
            'property_id' => 'required|exists:container_properties,id',
        ]);

        $container->update([
            'code' => $request->code,
            'shelve_id' => $request->shelve_id,
            'status_id' => $request->status_id,
            'property_id' => $request->property_id,
            'creator_id' => auth()->id(),
            'user_organisation_id' => auth()->user()->current8orgqnisqtion_id,
        ]);

        return redirect()->route('containers.index')->with('success', 'Container updated successfully.');
    }

    public function destroy(Container $container)
    {
        $container->delete();
        return redirect()->route('containers.index')->with('success', 'Container deleted successfully.');
    }
}
