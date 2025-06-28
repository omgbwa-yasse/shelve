<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Activity;
use App\Models\Building;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Floor;
use App\Models\Container;
use App\Models\RecordStatus;
use App\Models\Term;
use App\Models\CommunicationRecord;

class SearchReservationController extends Controller
{
    public function index(Request $request)
    {
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
                $query->where('code', $request->input('value'));
                break;

            case "operator":
                $query->where('operator_id', $request->input('id'));
                break;

            case "operator-organisation":
                $query->where('operator_organisation_id', $request->input('id'));
                break;

            case "user":
                $query->where('user_id', $request->input('id'));
                break;

            case "user-organisation":
                $query->where('user_organisation_id', $request->input('id'));
                break;

            case "return-available":
                $query->where('return_date', '>=', now()->format('Y-m-d'));
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
                $query->whereHas('status', function ($q) {
                    $q->where('name', 'approuvée');
                });
                break;

            case "InProgress":
                $query->whereHas('status', function ($q) {
                    $q->where('name', 'En examen');
                });
                break;

            default:
                // Ne rien faire, retourner toutes les réservations
                break;
        }

        // Ajout des relations nécessaires
        $query->with(['status', 'operator', 'operatorOrganisation', 'user', 'userOrganisation', 'records']);

        // Tri par date de création décroissante
        $query->orderBy('created_at', 'desc');

        $reservations = $query->paginate(10);

        return view('communications.reservations.index', compact('reservations'));
    }

    public function date()
    {
        return view('search.reservation.dateSearch');
    }
}
