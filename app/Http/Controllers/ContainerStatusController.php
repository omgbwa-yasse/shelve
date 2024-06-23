<?php

namespace App\Http\Controllers;
use App\Models\ContainerStatus;
use Illuminate\Http\Request;

class ContainerStatusController extends Controller
{

    public function index()
    {
        $containerStatuses = ContainerStatus::all();
        return view('containers.statuses.index', compact('containerStatuses'));
    }



    public function create()
    {
        return view('containers.statuses.create');
    }




    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:50|unique:container_statuses,name',
            'description' => 'nullable',
        ]);

        ContainerStatus::create([
            'name' => $request->name,
            'description' => $request->description,
            'creator_id' => auth()->id(),
        ]);

        return redirect()->route('container-status.index')->with('success', 'Container status created successfully.');
    }




    public function show(ContainerStatus $containerStatus)
    {
        return view('containers.statuses.show', compact('containerStatus'));
    }




    public function edit(ContainerStatus $containerStatus)
    {
        return view('containers.statuses.edit', compact('containerStatus'));
    }



    public function update(Request $request, ContainerStatus $containerStatus)
    {
        $request->validate([
            'name' => 'required|max:50|unique:container_statuses,name,' . $containerStatus->id,
            'description' => 'nullable',
        ]);

        $containerStatus->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('container-status.index')->with('success', 'Container status updated successfully.');
    }




    public function destroy(ContainerStatus $containerStatus)
    {
        $containerStatus->delete();
        return redirect()->route('container_statuses.index')->with('success', 'Container status deleted successfully.');
    }



}
