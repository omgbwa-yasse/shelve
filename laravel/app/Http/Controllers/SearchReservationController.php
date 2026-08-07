<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Reservation;
use App\Enums\ReservationStatus;
use App\Models\Activity;
use App\Models\Building;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Floor;
use App\Models\Container;
use App\Models\RecordStatus;

use App\Models\CommunicationRecord;

class SearchReservationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Reservation::query();

            switch($request->input('categ')) {
            case "dates":
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');

                if ($exactDate) {
                    $query->whereDate('created_at', $exactDate);
                } elseif ($startDate && $endDate) {
                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                }
                break;

            case "code":
                $value = $request->input('value');
                if ($value) {
                    $query->where('code', $value);
                }
                break;

            case "operator":
                $id = $request->input('id');
                if ($id) {
                    $query->where('operator_id', $id);
                }
                break;

            case "operator-organisation":
                $id = $request->input('id');
                if ($id) {
                    $query->where('operator_organisation_id', $id);
                }
                break;

            case "user":
                $id = $request->input('id');
                if ($id) {
                    $query->where('user_id', $id);
                }
                break;

            case "user-organisation":
                $id = $request->input('id');
                if ($id) {
                    $query->where('user_organisation_id', $id);
                }
                break;

            case "return-available":
                $query->where('return_date', '<=', now()->format('Y-m-d'))
                      ->whereNull('return_effective');
                break;

            case "not-return":
                $query->where('return_date', '<=', now()->format('Y-m-d'));
                break;

            case "unreturn":
                $query->whereNull('return_date');
                break;

            case "return-effective":
                $query->where('return_effective', '<=', now()->format('Y-m-d'));
                break;

            case "approved":
                $query->where('status', ReservationStatus::APPROVED);
                break;

            case "InProgress":
                $query->where('status', ReservationStatus::PENDING);
                break;

            default:
                // Ne rien faire, retourner toutes les réservations
                break;
        }

        // Ajout des relations nécessaires
        $query->with(['operator', 'operatorOrganisation', 'user', 'userOrganisation', 'records', 'communication']);

        // Tri par date de création décroissante
        $query->orderBy('created_at', 'desc');

        $reservations = $query->paginate(10);

        // Préparer les données pour la vue de recherche avancée
        $statuses = collect(\App\Enums\ReservationStatus::cases())->map(fn($status) => [
            'value' => $status->value,
            'label' => $status->label(),
        ]);
        $users = \App\Models\User::orderBy('name')->get();
        $organisations = \App\Models\Organisation::orderBy('name')->get();

        return view('communications.reservations.search', compact('reservations', 'statuses', 'users', 'organisations'));

        } catch (\Exception $e) {
            // En cas d'erreur, log l'erreur et retourner une vue avec des réservations vides
            Log::error('Erreur dans SearchReservationController: ' . $e->getMessage());
            $reservations = collect()->paginate(10);
            return view('communications.reservations.index', compact('reservations'));
        }
    }

    public function date()
    {
        return view('search.reservation.dateSearch');
    }
}
