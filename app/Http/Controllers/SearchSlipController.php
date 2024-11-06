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
        // Récupérer l'organisation de l'utilisateur connecté
        $userOrganisationId = auth()->user()->current_organisation_id;

        $query = Slip::query();

        // Appliquer le filtre d'organisation de base
        $query->where(function($q) use ($userOrganisationId) {
            $q->where('officer_organisation_id', $userOrganisationId)
                ->orWhere('user_organisation_id', $userOrganisationId);
        });

        switch($request->input('categ')) {
            case "dates":
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');

                if ($exactDate) {
                    $query->whereDate('created_at', $exactDate);
                }
                if ($startDate && $endDate) {
                    $query->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $endDate);
                    });
                }
                break;

            case "code":
                $query->where('code', $request->input('value'));
                break;

            case "officer":
                $query->where('operator_id', $request->input('id'));
                break;

            case "officer-organisation":
                $query->where('officer_organisation_id', $request->input('id'))
                    ->where('officer_organisation_id', $userOrganisationId); // Double vérification
                break;

            case "user":
                $query->where('user_id', $request->input('id'));
                break;

            case "user-organisation":
                $query->where('user_organisation_id', $request->input('id'))
                    ->where('user_organisation_id', $userOrganisationId); // Double vérification
                break;

            case "received":
                $query->where([
//                    'is_approved' => true,
                    'is_received' => true,
                    'is_integrated' => false
                ])->paginate(10);
                break;

            case "approved":
                $query->where([
                    'is_approved' => true,
                    'is_received' => true,
                    'is_integrated' => false
                ]);
                break;

            case "integrated":
                $query->where([
                    'is_approved' => true,
                    'is_received' => true,
                    'is_integrated' => true
                ]);
                break;

            case "project":
            case "draft":
            case "brouillon":
                $query->whereNotNull('created_at')
                    ->whereNotNull('name')
                    ->whereNotNull('code')
                    ->where('is_approved', false)
                    ->where('is_received', false)
                    ->where('is_integrated', false);
                break;

            default:
                $query->take(5);
                break;
        }

        // Appliquer la pagination
        $slips = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('slips.index', compact('slips'));
    }

    public function date()
    {
        return view('search.slips.dateSearch');
    }


    public function organisation()
    {
        $organisations = Organisation::all();
        $organisations->load('userSlips','officerSlips');
        return view('search.slips.organisationSearch', compact('organisations'));
    }

}

