<?php

namespace App\Http\Controllers;

use App\Models\ContainerProperty;
use Illuminate\Http\Request;

class ContainerPropertyController extends Controller
{
    public function index()
    {
        $containerProperties = ContainerProperty::all();

        return view('containers.properties.index', compact('containerProperties'));
    }

    public function create()
    {
        return view('containers.properties.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:container_properties|max:100',
            'width' => 'required|numeric',
            'length' => 'required|numeric',
            'depth' => 'required|numeric',
        ]);

        ContainerProperty::create([
            'name' => $request->name,
            'width' => $request->width,
            'length' => $request->length,
            'depth' => $request->depth,
            'creator_id' => auth()->id(),
        ]);

        return redirect()->route('container-property.index')
            ->with('success', 'Container property created successfully.');
    }

    public function show(ContainerProperty $containerProperty)
    {
        return view('containers.properties.show', compact('containerProperty'));
    }

    public function edit(ContainerProperty $containerProperty)
    {
        return view('containers.properties.edit', compact('containerProperty'));
    }

    public function update(Request $request, ContainerProperty $containerProperty)
    {
        $request->validate([
            'name' => 'required|unique:container_properties,name,'.$containerProperty->id.'|max:100',
            'width' => 'required|numeric',
            'length' => 'required|numeric',
            'depth' => 'required|numeric',
        ]);

        $containerProperty->update($request->all());

        return redirect()->route('container-property.index')
            ->with('success', 'Container property updated successfully.');
    }

    public function destroy(ContainerProperty $containerProperty)
    {
        $containerProperty->delete();

        return redirect()->route('container-property.index')
            ->with('success', 'Container property deleted successfully.');
    }
}
