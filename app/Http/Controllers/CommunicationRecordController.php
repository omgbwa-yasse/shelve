<?php

namespace App\Http\Controllers;



use App\Models\CommunicationRecord;
use App\Models\Communication;
use App\Models\Record;
use App\Models\User;
use Illuminate\Http\Request;

class CommunicationRecordController extends Controller
{

    public function index()
    {
        $communicationRecords = CommunicationRecord::with('record', 'operator', 'user')->get();
        return view('communications.records.index', compact('communicationRecords'));
    }



    public function create()
    {
        $records = Record::all();
        $users = User::all();
        return view('communications.records.create', compact('records', 'users'));
    }


    public function show(CommunicationRecord $communicationRecord)
    {
        $communicationRecord->load('record', 'operator', 'user');
        return view('communications.records.show', compact('communicationRecord'));
    }



    public function edit(CommunicationRecord $communicationRecord)
    {
        $records = Record::all();
        $users = User::all();
        return view('communications.records.edit', compact('communicationRecord', 'records', 'users'));
    }



    public function store(Request $request, communication $communication)
    {
        $request->validate([
            'code' => 'required|unique:communications,code',
            'operator_id' => 'required|exists:users,id',
            'operator_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'required|exists:users,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'return_date' => 'required|date',
            'return_effective' => 'nullable|date',
            'status_id' => 'required|exists:communication_statuses,id',
        ]);

        $communication = Communication::create([
            'code' => $request->code,
            'operator_id' => $request->operator_id,
            'operator_organisation_id' => $request->operator_organisation_id,
            'user_id' => $request->user_id,
            'user_organisation_id' => $request->user_organisation_id,
            'return_date' => $request->return_date,
            'return_effective' => $request->return_effective,
            'status_id' => $request->status_id,
        ]);

        return redirect()->route('communication-transactions.index')->with('success', 'Communication created successfully.');
    }




    public function update(Request $request, Communication $communication)
    {
        $request->validate([
            'code' => 'required|unique:communications,code,' . $communication->id,
            'operator_id' => 'required|exists:users,id',
            'operator_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'required|exists:users,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'return_date' => 'required|date',
            'return_effective' => 'nullable|date',
            'status_id' => 'required|exists:communication_statuses,id',
        ]);

        $communication->update([
            'code' => $request->code,
            'operator_id' => $request->operator_id,
            'operator_organisation_id' => $request->operator_organisation_id,
            'user_id' => $request->user_id,
            'user_organisation_id' => $request->user_organisation_id,
            'return_date' => $request->return_date,
            'return_effective' => $request->return_effective,
            'status_id' => $request->status_id,
        ]);

        return redirect()->route('communication-transactions.index')->with('success', 'Communication updated successfully.');
    }



    public function destroy(CommunicationRecord $communicationRecord)
    {
        $communicationRecord->delete();
        return redirect()->route('communications.records.index')->with('success', 'Communication record deleted successfully.');
    }
}


