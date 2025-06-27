<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Http\Requests\CommunicationStatusRequest;
use App\Models\CommunicationStatus;

class CommunicationStatusController extends Controller
{

    public function index()
    {
        $statuses = CommunicationStatus::all();
        return view('settings.communication-statuses.index', compact('statuses'));
    }


    public function create()
    {
        return view('settings.communication-statuses.create');
    }



    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:communication_statuses,name',
            'description' => 'nullable',
        ]);
        CommunicationStatus::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        return redirect()->route('communication-status.index')->with('success', 'Status created successfully.');
    }


    public function show(CommunicationStatus $communicationStatus)
    {
        return view('settings.communication-statuses.show', compact('communicationStatus'));
    }



    public function edit(CommunicationStatus $communicationStatus)
    {
        return view('settings.communication-statuses.edit', compact('communicationStatus'));
    }




    public function update(Request $request, CommunicationStatus $communicationStatus)
{
    $request->validate([
        'name' => 'required|unique:communication_statuses,name,' . $communicationStatus->id,
        'description' => 'required',
    ]);

    $communicationStatus->update([
        'name' => $request->name,
        'description' => $request->description,
    ]);

    return redirect()->route('communication-status.index')->with('success', 'Status updated successfully.');
}


    public function destroy(CommunicationStatus $communicationStatus)
    {
        $communicationStatus->delete();
        return redirect()->route('communication-status.index')->with('success', 'Status deleted successfully.');
    }


}
