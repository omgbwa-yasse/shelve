<?php

namespace App\Http\Controllers;

use App\Models\communicationRecord;
use App\Models\ReservationRecord;
use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Communication;
use App\Enums\ReservationStatus;
use App\Models\User;
use App\Models\Organisation;
use App\Services\CodeGeneratorService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with('operator', 'user', 'userOrganisation', 'operatorOrganisation', 'communication')->get();
        return view('communications.reservations.index', compact('reservations'));
    }



    public function show(Reservation $reservation)
    {
        $reservation->load(['operator', 'user', 'userOrganisation', 'operatorOrganisation', 'records']);
        return view('communications.reservations.show', compact('reservation'));
    }


    public function approved(Request $request)
    {
        Log::info('Méthode approved appelée avec les données: ', $request->all());

        $request->validate([
            'id' => 'required|exists:reservations,id'
        ]);

        DB::beginTransaction();
        try {
            // Récupérer la réservation avec ses relations
            $reservation = Reservation::with(['records', 'user', 'userOrganisation'])
                ->findOrFail($request->input('id'));

            Log::info('Réservation trouvée: ', ['id' => $reservation->id, 'code' => $reservation->code]);

            // Générer un code automatique pour la communication
            $codeGenerator = new CodeGeneratorService();
            $communicationCode = $codeGenerator->generateCommunicationCode();

            // Créer la nouvelle communication
            $communication = Communication::create([
                'code' => $communicationCode,
                'name' => $reservation->name,
                'content' => $reservation->content,
                'operator_id' => Auth::user()->id,
                'user_id' => $reservation->user_id,
                'user_organisation_id' => $reservation->user_organisation_id,
                'operator_organisation_id' => Auth::user()->current_organisation_id,
                'return_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                'status' => \App\Enums\CommunicationStatus::APPROVED,
            ]);

            // Mettre à jour la réservation avec l'ID de la communication
            $reservation->update([
                'communication_id' => $communication->id,
                'status' => ReservationStatus::APPROVED,
                'return_date' => Carbon::now()->addDays(14)->format('Y-m-d')
            ]);

            // Pour chaque record de la réservation
            foreach ($reservation->records as $record) {
                // Créer l'entrée dans communication_record
                communicationRecordPhysical::create([
                    'communication_id' => $communication->id,
                    'record_id' => $record->id,
                    'content' => null, // ou une valeur par défaut si nécessaire
                    'is_original' => true, // ou false selon votre logique
                    'return_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                    'return_effective' => null
                ]);

                // Supprimer l'entrée de reservation_record correspondante
                ReservationRecordPhysical::where([
                    'reservation_id' => $reservation->id,
                    'record_id' => $record->id
                ])->delete();
            }

            DB::commit();

            Log::info('Communication créée avec succès, redirection vers: ', [
                'communication_id' => $communication->id,
                'route' => 'communications.transactions.show'
            ]);

            return redirect()
                ->route('communications.transactions.show', ['transaction' => $communication->id])
                ->with('success', 'La réservation a été approuvée et convertie en communication.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'approbation de la réservation: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Une erreur est survenue lors de l\'approbation: ' . $e->getMessage());
        }
    }




    public function create()
    {
        $operators = User::all();
        $users = User::all();
        $statuses = collect(ReservationStatus::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label()
            ];
        });
        $organisations = Organisation::all();

        // Le code sera généré au moment de l'enregistrement pour éviter les conflits

        return view('communications.reservations.create', compact('operators', 'users', 'statuses', 'organisations'));
    }





    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:200',
                'content' => 'nullable|string',
                'user_id' => 'required|exists:users,id',
                'user_organisation_id' => 'required|exists:organisations,id',
                'status' => 'required|in:' . implode(',', array_map(fn($case) => $case->value, ReservationStatus::cases())),
            ]);

            if (!Auth::user()->current_organisation_id) {
                return redirect()->back()->withErrors(['error' => 'Vous devez avoir une organisation courante pour créer une réservation.'])->withInput();
            }

            // Générer un code automatique
            $codeGenerator = new CodeGeneratorService();
            $generatedCode = $codeGenerator->generateReservationCode();

            Reservation::create([
                'code' => $generatedCode,
                'name' => $request->name,
                'content' => $request->input('content'),
                'operator_id' => Auth::user()->id,
                'user_id' => $request->user_id,
                'user_organisation_id' => $request->user_organisation_id,
                'operator_organisation_id' => Auth::user()->current_organisation_id,
                'status' => ReservationStatus::from($request->status),
            ]);

            return redirect()->route('communications.reservations.index')
                ->with('success', 'Réservation créée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()])->withInput();
        }
    }



    public function edit(Reservation $reservation)
    {
        $operators = User::all();
        $users = User::all();
        $statuses = collect(ReservationStatus::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label()
            ];
        });
        $organisations = Organisation::all();
        return view('communications.reservations.edit', compact('reservation', 'operators', 'users', 'statuses', 'organisations'));
    }




    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'code' => 'required|unique:reservations,code,'.$reservation->id.'|max:10',
            'name' => 'required|string|max:200',
            'content' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'status' => 'required|in:' . implode(',', array_map(fn($case) => $case->value, ReservationStatus::cases())),
        ]);

        $reservation->update([
            'code' => $request->code,
            'name' => $request->name,
            'content' => $request->input('content'),
            'operator_id' => Auth::user()->id,
            'operator_organisation_id' => Auth::user()->current_organisation_id,
            'user_id' => $request->user_id,
            'user_organisation_id' => $request->user_organisation_id,
            'status' => ReservationStatus::from($request->status),
        ]);

        return redirect()->route('communications.reservations.index')
            ->with('success', 'Reservation updated successfully.');
    }




    public function destroy(Reservation $reservation)
    {
        $reservation->delete();

        return redirect()->route('communications.reservations.index')
            ->with('success', 'Reservation deleted successfully.');
    }

    /**
     * Display approved reservations with their communications.
     */
    public function listApproved()
    {
        $reservations = Reservation::with(['operator', 'user', 'userOrganisation', 'operatorOrganisation', 'communication', 'records'])
            ->where('status', ReservationStatus::APPROVED)
            ->whereNotNull('communication_id')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('communications.reservations.approved', compact('reservations'));
    }

    /**
     * Display approved reservations.
     */
    public function listApprovedReservations()
    {
        $reservations = Reservation::with(['operator', 'operatorOrganisation', 'user', 'userOrganisation', 'records', 'communication'])
            ->where('status', ReservationStatus::APPROVED)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('communications.reservations.approved_reservations', compact('reservations'));
    }

    /**
     * Display pending reservations.
     */
    public function pending()
    {
        return view('communications.reservations.pending');
    }

    public function returnAvailable()
    {
        $reservations = Reservation::with(['operator', 'operatorOrganisation', 'user', 'userOrganisation', 'records', 'communication'])
            ->where('return_date', '<=', now()->format('Y-m-d'))
            ->whereNull('return_effective')
            ->orderBy('return_date', 'asc')
            ->paginate(15);

        return view('communications.reservations.return_available', compact('reservations'));
    }

    public function markAsReturned(Request $request, Reservation $reservation)
    {
        $reservation->update([
            'return_effective' => now()->format('Y-m-d')
        ]);

        // Également mettre à jour la communication associée si elle existe
        if ($reservation->communication) {
            $reservation->communication->update([
                'return_effective' => now()->format('Y-m-d')
            ]);
        }

        return response()->json(['success' => true]);
    }
}
