<?php

namespace App\Http\Controllers;

use App\Models\CommunicationRecord;
use App\Models\Communication;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommunicationRecordController extends Controller
{
    public function index(INT $id)
    {
        $communication = communication ::findOrFail($id);
        $communicationRecords = CommunicationRecord::where('communication_id', $communication->id)->get();
        $communicationRecords->load('communication', 'record');

        return view('communications.records.index', compact('communicationRecords','communication'));
    }

    public function create(INT $id)
    {
        $communication = Communication::findOrFail($id);
        $users = User::all();
        return view('communications.records.create', compact('communication', 'users'));
    }




    public function show(INT $id, INT $idRecord)
    {
        $communicationRecord = communicationRecord::findOrFail($idRecord);
        $communicationRecord->load('record','communication');
        $communication = communication::findOrFail($id);
        return view('communications.records.show', compact('communicationRecord', 'communication'));
    }




    public function edit(Communication $communication, CommunicationRecord $communicationRecord)
    {
        $records = Record::all();
        $users = User::all();
        return view('communications.records.edit', compact('communicationRecord', 'communication', 'records', 'users'));
    }




    public function store(Request $request, INT $id)
    {
        $request->validate([
            'record_id' => 'required|exists:records,id',
            'is_original' => 'required|in:0,1',
            'content' => 'nullable|string',
        ]);

        $communication = Communication::findOrFail($id);

        $communicationRecord = CommunicationRecord::create([
            'communication_id' => $communication->id,
            'content' => $request->input('content'),
            'record_id' => $request->record_id,
            'is_original' => (int)$request->is_original,
            'return_date' => date('Y-m-d', strtotime("+14 days")),
        ]);


        return redirect()->route('communications.transactions.show', $communication->id)->with('success', 'Communication request created successfully.');
    }





    public function update(Request $request, CommunicationRecord $communicationRecord)
    {
        $request->validate([
            'record_id' => 'required|exists:records,id',
            'is_original' => 'required|boolean',
            'content' => 'nullable|string',
        ]);

        $communicationRecord->update($request->all());
        return redirect()->route('communications.transactions.index')->with('success', 'Communication updated successfully.');
    }




    public function returnEffective(Request $request)
    {
        $communicationRecord = CommunicationRecord::findOrFail($request->input('id'));
        $communicationRecord->update(['return_effective' => now(), 'operator_id' => Auth()->user()->getAuthIdentifier() ]);  // Ajouter operateur qui fait le retour effectif
        $communication = $communicationRecord ->communication;
        return redirect()->route('communications.transactions.show', $communication)->with('success', 'Communication updated successfully.');
    }


    public function returnCancel(Request $request)
    {
        $communicationRecord = CommunicationRecord::findOrFail($request->input('id'));
        $communicationRecord->update(['return_effective' => NULL]);
        $communication = $communicationRecord->communication;
        return redirect()->route('communications.transactions.show', $communication)->with('success', 'Communication updated successfully.');
    }



    public function destroy(INT $communication_id, INT $record_id)
    {
        $communication = Communication::findOrFail($communication_id);
        $communication->records()->where('id', $record_id)->delete();
        return redirect()->route('communications.transactions.show',$communication_id)->with('success', 'Communication record deleted successfully.');
    }

    public function searchRecords(Request $request)
    {
        $query = $request->get('q');

        // Vérifier que la requête fait au moins 3 caractères
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        // Rechercher les archives par nom ou code, limiter à 5 résultats
        $records = Record::where('name', 'LIKE', '%' . $query . '%')
                        ->orWhere('code', 'LIKE', '%' . $query . '%')
                        ->select('id', 'name', 'code')
                        ->limit(5)
                        ->get();

        return response()->json($records);
    }

}



