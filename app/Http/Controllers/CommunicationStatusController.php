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
        return view('communications.statuses.index', compact('statuses'));
    }


    public function create()
    {
        return view('communications.statuses.create');
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


    public function show(INt $id)
    {
        $status = CommunicationStatus::findOrFail($id);
        return view('communications.statuses.show', compact('status'));
    }



    public function edit(INT $id)
    {
        $status = CommunicationStatus::findOrFail($id);
        return view('communications.statuses.edit', compact('status'));
    }




    public function update(Request $request, CommunicationStatus $status)
{
    $request->validate([
        'name' => 'required|unique:communication_statuses,name,' . $status->id,
        'description' => 'required',
    ]);

    $status->update([
        'name' => $request->name,
        'description' => $request->description,
    ]);

    return redirect()->route('communication-status.index')->with('success', 'Status updated successfully.');
}


    public function destroy(CommunicationStatus $status)
    {
        $status->delete();
        return redirect()->route('communication-status.index')->with('success', 'Status deleted successfully.');
    }


}
