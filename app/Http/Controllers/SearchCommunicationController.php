<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\communication;
use App\Models\Activity;
use App\Models\Building;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\floor;
use App\Models\Container;
use App\Models\RecordStatus;
use App\Models\Term;
use App\Models\communicationRecord;


class SearchCommunicationController extends Controller
{
    public function index(Request $request)
    {
        $communications = '';

        switch($request->input('categ')){

            case "dates":
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');
                $query = communication::query();

                if ($exactDate) {
                    $query->whereDate('created_at', $exactDate);
                }

                if ($startDate && $endDate) {
                    $query->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $endDate);
                    });
                }

                $communications = $query->get();
                break;



            case "code":
                $communications = communication::where('code', $request->input('value'))
                    ->get();
                break;



            case "operator":
                $communications = communication::where('operator_id', $request->input('id'))
                    ->get();
                break;



            case "operator-organisation":
                $communications = communication::where('operator_organisation_id', $request->input('id'))
                    ->get();
                break;


            case "user":
                $communications = communication::where('user_id', $request->input('id'))
                    ->get();
                break;


            case "user-organisation":
                $communications = communication::where('user_organisation_id', $request->input('id'))
                    ->get();
                break;


            case "return-available":
                $communications = communication::where('return_date','>=', now()->format('Y-m-d'))
                    ->get();
                break;


            case "not-return":
                    $communications = communication::where('return_date','<=', now()->format('Y-m-d'))
                        ->get();
                    break;

            case "unreturn":
                $communications = communication::where('return_date', NULL)
                    ->get();
                break;


            case "return-effective":
                $communications = communication::where('return_effective', '<=', now()->format('Y-m-d'))
                    ->get();
                break;


            default:
                $communications = communication::take(5)->get();
                break;
        }


        return view('communications.index', compact('communications'));
    }


    public function date()
    {
        return view('search.communication.dateSearch');
    }

}

