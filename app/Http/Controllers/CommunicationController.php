<?php

namespace App\Http\Controllers;


use App\Exports\CommunicationsExport;
use App\Models\Communication;
use App\Models\Organisation;
use App\Models\User;
use App\Services\CodeGeneratorService;
use App\Services\RateLimitService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class CommunicationController extends Controller
{


    public function index()
    {
        $query = Communication::with('operator', 'operatorOrganisation','records','user', 'userOrganisation');

        if (!Auth::user()->isSuperAdmin()) {
            $query->forOrganisation(Auth::user()->current_organisation_id);
        }

        $communications = $query->paginate(10);
        return view('communications.index', compact('communications'));
    }



    public function create()
    {
        $users = User::all();
        $statuses = $this->getFormattedStatuses();
        $organisations = Organisation::all();

        // Le code sera généré au moment de l'enregistrement pour éviter les conflits

        return view('communications.create', compact('users', 'statuses', 'organisations'));
    }

    /**
     * Get formatted statuses for forms
     */
    private function getFormattedStatuses(): array
    {
        $formattedStatuses = [];
        foreach (\App\Enums\CommunicationStatus::getAll() as $status) {
            $formattedStatuses[] = [
                'value' => $status->value,
                'label' => $status->label()
            ];
        }
        return $formattedStatuses;
    }

    /**
     * Get status values for validation
     */
    private function getStatusValues(): array
    {
        return array_map(fn($s) => $s->value, \App\Enums\CommunicationStatus::getAll());
    }




    public function store(Request $request, RateLimitService $rateLimitService)
    {
        // Vérifier le rate limiting
        if ($rateLimitService->tooManyAttempts('communication_create')) {
            return redirect()->back()
                ->withErrors(['rate_limit' => $rateLimitService->getErrorMessage('communication_create')])
                ->withInput();
        }

        // Validation des données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'return_date' => 'required|date|after_or_equal:today',
            'user_organisation_id' => 'required|exists:organisations,id',
            'status' => 'required|in:' . implode(',', $this->getStatusValues()),
        ]);

        // Vérifier l'organisation courante
        if (!Auth::user()->current_organisation_id) {
            return redirect()->back()
                ->withErrors(['error' => 'Vous devez avoir une organisation courante pour créer une communication.'])
                ->withInput();
        }

        // Créer la communication avec rate limiting
        $communication = $rateLimitService->attempt(
            'communication_create',
            function() use ($validated) {
                $codeGenerator = new CodeGeneratorService();
                $generatedCode = $codeGenerator->generateCommunicationCode();

                return Communication::create([
                    'code' => $generatedCode,
                    'name' => $validated['name'],
                    'content' => $validated['content'] ?? null,
                    'operator_id' => Auth::user()->id,
                    'user_id' => $validated['user_id'],
                    'user_organisation_id' => $validated['user_organisation_id'],
                    'operator_organisation_id' => Auth::user()->current_organisation_id,
                    'return_date' => $validated['return_date'],
                    'status' => $validated['status'],
                ]);
            }
        );

        if (!$communication) {
            return redirect()->back()
                ->withErrors(['rate_limit' => $rateLimitService->getErrorMessage('communication_create')])
                ->withInput();
        }

        return redirect()->route('communications.transactions.show', $communication)
            ->with('success', 'Communication créée avec succès');
    }




    public function show(int $id)
    {
        $communication = Communication::with('operator', 'operatorOrganisation', 'user', 'userOrganisation')->findOrFail($id);
        $this->authorize('view', $communication);
        return view('communications.show', compact('communication'));
    }




    public function edit(int $id)
    {
        $communication = Communication::with('operator', 'operatorOrganisation', 'user', 'userOrganisation')->findOrFail($id);
        $this->authorize('update', $communication);

        // Empêcher l'édition si la communication est retournée
        if ($communication->isReturned()) {
            return redirect()->route('communications.transactions.show', $id)
                ->with('error', 'Cette communication ne peut plus être modifiée car elle a été retournée.');
        }

        $users = User::all();
        $statuses = $this->getFormattedStatuses();
        $organisations = Organisation::all();
        return view('communications.edit', compact('organisations', 'communication', 'users', 'statuses'));
    }


    public function transmission(Request $request)
    {
        $communication = Communication::findOrFail($request->input('id'));

        // Utiliser la nouvelle logique de changement de statut
        if($communication->return_effective == null && !$communication->isReturned()){
            $communication->changeStatus('transmit');
        }

        return redirect()->route('communications.transactions.show', $communication)->with('success', 'Documents transmis - Communication en consultation.');
    }

    public function validateCommunication(Request $request)
    {
        $communication = Communication::findOrFail($request->input('id'));

        // Valider seulement si en cours de demande
        if($communication->isPending() && !$communication->isReturned()){
            $communication->changeStatus('validate');            return redirect()->route('communications.transactions.show', $communication)->with('success', 'Communication validée avec succès.');
        }
        return redirect()->route('communications.transactions.show', $communication)->with('error', 'Cette communication ne peut pas être validée dans son état actuel.');
    }

    public function reject(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:communications,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $communication = Communication::findOrFail($request->input('id'));

        // Rejeter si en cours de demande ou validée
        if(($communication->isPending() || $communication->isApproved()) && !$communication->isReturned()){
            $communication->changeStatus('reject');

            // Optionnellement stocker la raison du rejet
            if($request->filled('reason')) {
                $communication->update(['content' => $communication->content . "\n\nRaison du rejet: " . $request->reason]);
            }            return redirect()->route('communications.transactions.show', $communication)->with('success', 'Communication rejetée.');
        }
        return redirect()->route('communications.transactions.show', $communication)->with('error', 'Cette communication ne peut pas être rejetée dans son état actuel.');
    }


    public function returnEffective(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:communications,id',
        ]);

        $communication = Communication::findOrFail($request->input('id'));

        if($communication->return_effective == null){
            // Changer le statut et mettre à jour la date de retour
            $communication->changeStatus('return');
            $communication->update(['return_effective' => now()]);

            // Mettre à jour tous les records liés qui ne sont pas encore retournés
            $communication->records()->whereNull('return_effective')->update([
                'return_effective' => now(),
            ]);
        }

        return redirect()->route('communications.transactions.show', $communication)->with('success', 'Communication marquée comme retournée avec succès.');
    }


    public function returnCancel(Request $request)
    {
        $communication = Communication::findOrFail($request->input('id'));

        if($communication->return_effective != null){
            // Utiliser la nouvelle logique pour revenir en consultation
            $communication->changeStatus('cancel_return');
            $communication->update(['return_effective' => null]);

            // Annuler le retour effectif de tous les records liés
            $communication->records()->whereNotNull('return_effective')->update([
                'return_effective' => null,
            ]);
        }

        return redirect()->route('communications.transactions.show', $communication)->with('success', 'Retour effectif annulé avec succès.');
    }


    public function update(Request $request, Communication $communication)
    {
        $this->authorize('update', $communication);

        // Empêcher la modification si la communication est retournée
        if ($communication->isReturned()) {
            return redirect()->route('communications.transactions.show', $communication->id)
                ->with('error', 'Cette communication ne peut plus être modifiée car elle a été retournée.');
        }

        $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:200',
            'content' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'return_date' => 'required|date',
            'status' => 'required|in:' . implode(',', $this->getStatusValues()),
            'user_organisation_id' => 'required|exists:organisations,id',
        ]);

        $communication->update([
            'code' => $request->code,
            'name' => $request->name,
            'content' => $request->input('content'),
            'user_id' => $request->user_id,
            'user_organisation_id' => $request->user_organisation_id,
            'return_date' => $request->return_date,
            'status' => $request->status,
        ]);

        return redirect()->route('communications.transactions.show', $communication)->with('success', 'Communication mise à jour avec succès.');
    }




    public function destroy(int $communication_id)
    {
        $communication = Communication::with('records')->findOrFail($communication_id);
        $this->authorize('delete', $communication);

        // Empêcher la suppression si la communication est retournée
        if ($communication->isReturned()) {
            return redirect()->route('communications.transactions.index')
                ->with('error', 'Cette communication ne peut pas être supprimée car elle a été retournée.');
        }

        if ($communication->records->isEmpty()) {
            $communication->delete();
            return redirect()->route('communications.transactions.index')->with('success', 'Communication supprimée avec succès.');
        } else {
            return redirect()->route('communications.transactions.index')->with('error', 'Vous ne pouvez pas supprimer cette communication car elle contient des documents.');
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

        $pdf = Pdf::loadView('communications.print', compact('communications'));
        return $pdf->download('communications_print.pdf');
    }
}
