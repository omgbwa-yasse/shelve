<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slip;
use App\Models\Activity;
use App\Models\Building;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\floor;
use App\Models\Container;
use App\Models\Organisation;
use App\Models\RecordStatus;
use App\Models\Term;
use App\Models\SlipRecord;


class SearchSlipController extends Controller
{
    public function index(Request $request)
    {
        $slips = '';

        switch($request->input('categ')){

            case "dates":
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');
                $query = Slip::query();

                if ($exactDate) {
                    $query->whereDate('created_at', $exactDate);
                }

                if ($startDate && $endDate) {
                    $query->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $endDate);
                    });
                }

                $slips = $query->get();
                break;



            case "code":
                $slips = Slip::where('code', $request->input('value'))
                    ->get();
                break;



            case "officer":
                $slips = Slip::where('operator_id', $request->input('id'))
                    ->get();
                break;



            case "officer-organisation":
                $slips = Slip::where('officer_organisation_id', $request->input('id'))
                    ->get();
                break;


            case "user":
                $slips = Slip::where('user_id', $request->input('id'))
                    ->get();
                break;


            case "user-organisation":
                $slips = Slip::where('user_organisation_id', $request->input('id'))
                    ->get();
                break;


            case "approved":
                    $slips = Slip::where('is_approved', true)
                    ->get();
                    break;

            case "draft":
                    $slips = Slip::where('is_approved', false)
                    ->get();
                    break;

            default:
                $slips = Slip::take(5)->get();
                break;
        }


        return view('transferrings.slips.index', compact('slips'));
    }


    public function date()
    {
        return view('search.transferring.dateSearch');
    }


    public function organisation()
    {
        $organisations = Organisation::all();
        $organisations->load('userSlips','officerSlips');
        return view('search.transferring.organisationSearch', compact('organisations'));
    }

}

