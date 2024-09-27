<?php

namespace App\Http\Controllers;


use App\Exports\CommunicationsExport;
use App\Http\Requests\CommunicationRequest;
use App\Models\Communication;
use App\Models\CommunicationStatus;
use App\Models\Dolly;
use App\Models\DollyCommunication;
use App\Models\Organisation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CommunicationController extends Controller
{


    public function index()
    {
        $communications = Communication::with('operator', 'operatorOrganisation','records','user', 'userOrganisation')->paginate(10);
        return view('communications.index', compact('communications'));
    }
    public function addToCart(Request $request)
    {
        $communicationIds = $request->input('communications');
        $dolly = Dolly::firstOrCreate(['user_id' => auth()->id()]);

        foreach ($communicationIds as $communicationId) {
            DollyCommunication::firstOrCreate([
                'communication_id' => $communicationId,
                'dolly_id' => $dolly->id,
            ]);
        }

        return response()->json(['message' => 'Communications ajoutées au chariot avec succès']);
    }


    public function create()
    {
        $users = User::all();
        $statuses = CommunicationStatus::all();
        $organisations = Organisation::all();
        return view('communications.create', compact('users', 'statuses','organisations'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:communications,code',
            'name' => 'required|string|max:200',
            'content' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'return_date' => 'required|date',
            'user_organisation_id' => 'required|exists:organisations,id',
        ]);

        Communication::create([
            'code' => $request->code,
            'name' => $request->name,
            'content' => $request->input('content'),
            'operator_id' => Auth()->user()->id,
            'user_id' => $request->user_id,
            'user_organisation_id' => $request->user_organisation_id,
            'operator_organisation_id' => Auth()->user()->organisation->id,
            'return_date' => $request->return_date,
            'status_id' => 1,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Communication created successfully.');
    }



    public function show(INT $id)
    {
        $communication = Communication::with('operator', 'operatorOrganisation', 'user', 'userOrganisation')->findOrFail($id);
        return view('communications.show', compact('communication'));
    }




    public function edit(INT $id)
    {
        $communication = Communication::with('operator', 'operatorOrganisation', 'user', 'userOrganisation')->findOrFail($id);
        $users = User::all();
        $statuses = CommunicationStatus::all();
        $organisations = Organisation::all();
        return view('communications.edit', compact('organisations','communication', 'users', 'statuses'));
    }


    public function tranmission(Request $request)
    {
        $communication = Communication::findOrFail($request->input('id'));
        if($communication->return_effective == NULL){
            $communication->update([
                'status_id' => 2, // traités
            ]);
        }
        return view('communications.show', compact('communication'));
    }


    public function returnEffective(Request $request)
    {
        $communication = Communication::findOrFail($request->input('id'));
        if($communication->return_effective == NULL){
            $communication->update([
                'return_effective' => Now(),
                'status_id' => 3, // traités
            ]);
        }
        return view('communications.show', compact('communication'));
    }


    public function returnCancel(Request $request)
    {
        $communication = Communication::findOrFail($request->input('id'));
        if($communication->return_effective != NULL){
            $communication->update([
                'return_effective' => NULL,
            ]);
        }
        return view('communications.show', compact('communication'));
    }


    public function update(Request $request, Communication $communication)
    {
        $request->validate([
            'code' => 'required|unique:communications,code,' . $communication->id,
            'name' => 'required|string|max:200',
            'content' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'return_date' => 'required|date',
            'status_id' => 'required|exists:communication_statuses,id',
        ]);

        $communication->update([
            'code' => $request->code,
            'name' => $request->name,
            'content' => $request->input('content'),
            'operator_id' => Auth()->user()->id,
            'operator_organisation_id' => Auth()->user()->organisation->id,
            'user_id' => $request->user_id,
            'user_organisation_id' => $request->user_organisation_id,
            'return_date' => $request->return_date,
            'status_id' => 1,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Communication updated successfully.');
    }




    public function destroy(INT $communication_id)
    {
        $communication = Communication::with('records')->findOrFail($communication_id);
        if ($communication->records->isEmpty()) {
            $communication->delete();
            return redirect()->route('transactions.index')->with('success', 'Communication deleted successfully.');
        } else {
            return redirect()->route('transactions.index')->with('error', 'You cannot delete this communication.');
        }
    }



    public function export(Request $request)
    {
        $communicationIds = explode(',', $request->query('communications'));
        $communications = Communication::whereIn('id', $communicationIds)->get();

        return Excel::download(new CommunicationsExport($communications), 'communications_export.xlsx');
    }

    public function print(Request $request)
    {
        $communicationIds = explode(',', $request->query('communications'));
        $communications = Communication::with([
            'user',
            'userOrganisation',
            'operator',
            'operatorOrganisation',
            'status',
            'records.record' => function ($query) {
                $query->with([
                    'status',
                    'support',
                    'level',
                    'activity',
                    'parent',
                    'container',
                    'user',
                    'authors',
                    'terms',
                    'attachments',
                    'children'
                ]);
            }
        ])
            ->whereIn('id', $communicationIds)
            ->get();

        // Uncomment the following line for debugging
        // dd($communications);

        $pdf = PDF::loadView('communications.print', compact('communications'));
        return $pdf->download('communications_print.pdf');
    }
    }


