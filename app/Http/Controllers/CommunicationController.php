<?php

namespace App\Http\Controllers;


use App\Exports\CommunicationsExport;
use App\Http\Requests\CommunicationRequest;
use App\Models\Communication;
use App\Models\communicationRecord;
use App\Models\CommunicationStatus;
use App\Models\Dolly;
use App\Models\DollyCommunication;
use App\Models\Organisation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class CommunicationController extends Controller
{


    public function index()
    {
        $communications = Communication::with('operator', 'operatorOrganisation','records','user', 'userOrganisation')->paginate(10);
        return view('communications.index', compact('communications'));
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
        try {
            $request->validate([
                'code' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'content' => 'nullable|string',
                'user_id' => 'required|exists:users,id',
                'return_date' => 'required|date|after_or_equal:today',
                'user_organisation_id' => 'required|exists:organisations,id',
                'status_id' => 'required|exists:communication_statuses,id',
            ]);

            // Vérifier que l'utilisateur a une organisation courante
            if (!Auth::user()->current_organisation_id) {
                return redirect()->back()->withErrors(['error' => 'Vous devez avoir une organisation courante pour créer une communication.'])->withInput();
            }

            Communication::create([
                'code' => $request->code,
                'name' => $request->name,
                'content' => $request->content,
                'operator_id' => Auth::user()->id,
                'user_id' => $request->user_id,
                'user_organisation_id' => $request->user_organisation_id,
                'operator_organisation_id' => Auth::user()->current_organisation_id,
                'return_date' => $request->return_date,
                'status_id' => $request->status_id,
            ]);

            return redirect()->route('transactions.index')->with('success', 'Communication créée avec succès');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()])->withInput();
        }
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


    public function transmission(Request $request)
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
        $request->validate([
            'id' => 'required|exists:communications,id',
        ]);
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
            'code' => 'required' . $communication->id,
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
            'operator_organisation_id' => Auth()->user()->current_organisation_id,
            'user_id' => $request->user_id,
            'user_organisation_id' => $request->user_organisation_id,
            'return_date' => $request->return_date,
            'status_id' => 1,
        ]);

        return redirect()->back()->with('success', 'Communication updated successfully.');
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
        $id = $request->id;

        $communications = Communication::where('id', $id)->get();

        if ($communications->isEmpty()) {
            return redirect()->back()->with('error', 'No communications found to export.');
        }

        return Excel::download(new CommunicationsExport($communications), 'communications_export.xlsx');
    }

    public function print(Request $request)
    {

        $communications = Communication::where('id', $request->id)->get();

        if ($communications->isEmpty()) {
            return redirect()->back()->with('error', 'No communications found to print.');
        }

        $communications->load([
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
                    'user',
                    'authors',
                    'terms',
                    'attachments',
                    'children'
                ]);
            }
        ]);

        $pdf = PDF::loadView('communications.print', compact('communications'));
        return $pdf->download('communications_print.pdf');
    }
}
