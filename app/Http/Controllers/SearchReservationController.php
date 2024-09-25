<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\reservation;
use App\Models\Activity;
use App\Models\Building;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\floor;
use App\Models\Container;
use App\Models\RecordStatus;
use App\Models\Term;
use App\Models\communicationRecord;


class SearchReservationController extends Controller
{
    public function index(Request $request)
    {
        $reservations = '';

        switch($request->input('categ')){

            case "dates":
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');
                $query = reservation::query();

                if ($exactDate) {
                    $query->whereDate('created_at', $exactDate);
                }

                if ($startDate && $endDate) {
                    $query->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $endDate);
                    });
                }

                $reservations = $query->paginate(10);
                break;



            case "code":
                $reservations = reservation::where('code', $request->input('value'))
                    ->paginate(10);
                break;



            case "operator":
                $reservations = reservation::where('operator_id', $request->input('id'))
                    ->paginate(10);
                break;



            case "operator-organisation":
                $reservations = reservation::where('operator_organisation_id', $request->input('id'))
                    ->paginate(10);
                break;


            case "user":
                $reservations = reservation::where('user_id', $request->input('id'))
                    ->paginate(10);
                break;


            case "user-organisation":
                $reservations = reservation::where('user_organisation_id', $request->input('id'))
                    ->paginate(10);
                break;


            case "return-available":
                $reservations = reservation::where('return_date','>=', now()->format('Y-m-d'))
                    ->paginate(10);
                break;


            case "not-return":
                    $reservations = reservation::where('return_date','<=', now()->format('Y-m-d'))
                        ->paginate(10);
                    break;

            case "unreturn":
                $reservations = reservation::where('return_date', NULL)
                    ->paginate(10);
                break;


            case "return-effective":
                $reservations = reservation::where('return_effective', '<=', now()->format('Y-m-d'))
                    ->paginate(10);
                break;


            case "approved":
                $reservations = reservation::whereHas('status', function ($query) {
                    $query->where('name', 'approuvée');
                })->paginate(10);
                break;


            case "InProgress":
                $reservations = reservation::whereHas('status', function ($query) {
                    $query->where('name', 'En examen');
                })->paginate(10);
                break;


            default:
                $reservations = reservation::take(5)->paginate(10);
                break;
        }


        return view('reservations.index', compact('reservations'));
    }


    public function date()
    {
        return view('search.reservation.dateSearch');
    }

}

