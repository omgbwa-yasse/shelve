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
    public function index(Communication $communication)
    {
        $communicationRecords = CommunicationRecord::where('communication_id', $communication->id)->get();
        $communicationRecords->load('communication', 'record');

        return view('communications.records.index', compact('communicationRecords', 'communication'));
    }

    public function create(Communication $communication)
    {
        $users = User::all();
        return view('communications.records.create', compact('communication', 'users'));
    }

    public function show(Communication $communication, CommunicationRecord $communicationRecord)
    {
        // ⚠️ ATTENTION: Le nom du paramètre doit correspondre au nom dans la route
        // Si ta route utilise {record}, change le paramètre pour $record
        // Si ta route utilise {communicationRecord}, garde $communicationRecord

        $communicationRecord->load('record', 'communication');
        return view('communications.records.show', compact('communicationRecord', 'communication'));
    }

    public function edit(Communication $communication, CommunicationRecord $communicationRecord)
    {
        $records = Record::all();
        $users = User::all();
        return view('communications.records.edit', compact('communicationRecord', 'communication', 'records', 'users'));
    }

    public function store(Request $request, Communication $communication)
    {
        $request->validate([
            'record_id' => 'required|exists:records,id',
            'is_original' => 'required|in:0,1',
            'content' => 'nullable|string',
        ]);

        // Debug pour voir si $communication est bien récupéré
        if (!$communication || !$communication->id) {
            // Fallback: récupérer l'ID depuis la route ou la request
            $communicationId = $request->route('communication') ?? $request->input('communication_id');
            if ($communicationId) {
                $communication = Communication::findOrFail($communicationId);
            } else {
                abort(400, 'Communication ID manquant');
            }
        }

        $communicationRecord = CommunicationRecord::create([
            'communication_id' => $communication->id,
            'content' => $request->input('content'),
            'record_id' => $request->record_id,
            'is_original' => (int)$request->is_original,
            'return_date' => date('Y-m-d', strtotime("+14 days")),
        ]);

        return redirect()->route('communications.transactions.show', $communication->id)->with('success', 'Communication request created successfully.');
    }

    public function update(Request $request, Communication $communication, CommunicationRecord $communicationRecord)
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
        $communicationRecord->update(['return_effective' => now(), 'operator_id' => auth()->user()->id]);
        $communication = $communicationRecord->communication;
        return redirect()->route('communications.transactions.show', $communication)->with('success', 'Communication updated successfully.');
    }

    public function returnCancel(Request $request)
    {
        $communicationRecord = CommunicationRecord::findOrFail($request->input('id'));
        $communicationRecord->update(['return_effective' => null]);
        $communication = $communicationRecord->communication;
        return redirect()->route('communications.transactions.show', $communication)->with('success', 'Communication updated successfully.');
    }

    public function destroy(Communication $communication, CommunicationRecord $communicationRecord)
    {
        $communicationRecord->delete();
        return redirect()->route('communications.transactions.show', $communication->id)->with('success', 'Communication record deleted successfully.');
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
