<?php

namespace App\Http\Controllers;

use App\Models\RecordLevel;
use App\Models\RecordSupport;
use App\Models\SlipStatus;
use App\Models\User;
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

    public function form()
    {
        $data = [
            'statuses' => SlipStatus::select('id', 'name')->get(),
            'officers' => User::select('id', 'name')->get(),
            'users' => User::select('id', 'name')->get(),
            'organisations' => Organisation::select('id', 'name')->get(),
            'activities' => Activity::select('id', 'name')->get(),
            'levels' => RecordLevel::select('id', 'name')->get(),
            'supports' => RecordSupport::select('id', 'name')->get(),
            'containers' => Container::select('id', 'code')->get(),
        ];

        return view('search.slips.advanced', compact('data'));
    }

    public function advanced(Request $request)
    {
        $fields = $request->input('field');
        $operators = $request->input('operator');
        $values = $request->input('value');

        $query = Slip::query();

        if ($fields && $operators && $values) {
            foreach ($fields as $index => $field) {
                $operator = $operators[$index];
                $value = $values[$index];

                switch ($field) {
                    case 'code':
                    case 'name':
                    case 'description':
                        $this->applyTextSearch($query, $field, $operator, $value);
                        break;

                    case 'received_date':
                    case 'approved_date':
                    case 'integrated_date':
                        $this->applyDateSearch($query, $field, $operator, $value);
                        break;

                    case 'officer':
                        $this->applyUserSearch($query, 'officer_id', $operator, $value);
                        break;

                    case 'user':
                        $this->applyUserSearch($query, 'user_id', $operator, $value);
                        break;

                    case 'officer_organisation':
                        $this->applyOrganisationSearch($query, 'officer_organisation_id', $operator, $value);
                        break;

                    case 'user_organisation':
                        $this->applyOrganisationSearch($query, 'user_organisation_id', $operator, $value);
                        break;

                    case 'slip_status':
                        $this->applyStatusSearch($query, $operator, $value);
                        break;

                    case 'received_by':
                    case 'approved_by':
                    case 'integrated_by':
                        $this->applyAgentSearch($query, $field, $operator, $value);
                        break;

                    case 'record':
                        $this->applyRecordSearch($query, $operator, $value);
                        break;

                    case 'container':
                        $this->applyContainerSearch($query, $operator, $value);
                        break;
                }
            }
        }

        $slips = $query->with([
            'officerOrganisation',
            'officer',
            'userOrganisation',
            'user',
            'slipStatus',
            'records.level',
            'records.support',
            'records.activity',
            'records.containers',
            'receivedAgent',
            'approvedAgent',
            'integratedAgent'
        ])->paginate(20);

        return view('slips.index', compact('slips'));
    }

    private function applyTextSearch($query, $field, $operator, $value)
    {
        switch ($operator) {
            case 'commence par':
                $query->where($field, 'like', $value . '%');
                break;
            case 'contient':
                $query->where($field, 'like', '%' . $value . '%');
                break;
            case 'ne contient pas':
                $query->where($field, 'not like', '%' . $value . '%');
                break;
        }
    }

    private function applyDateSearch($query, $field, $operator, $value)
    {
        switch ($operator) {
            case '=':
                $query->whereDate($field, '=', $value);
                break;
            case '>':
                $query->whereDate($field, '>', $value);
                break;
            case '<':
                $query->whereDate($field, '<', $value);
                break;
        }
    }

    private function applyUserSearch($query, $field, $operator, $value)
    {
        if ($operator === 'avec') {
            $query->where($field, $value);
        } else {
            $query->where($field, '!=', $value)
                ->orWhereNull($field);
        }
    }

    private function applyOrganisationSearch($query, $field, $operator, $value)
    {
        if ($operator === 'avec') {
            $query->where($field, $value);
        } else {
            $query->where($field, '!=', $value)
                ->orWhereNull($field);
        }
    }

    private function applyStatusSearch($query, $operator, $value)
    {
        if ($operator === 'avec') {
            $query->where('slip_status_id', $value);
        } else {
            $query->where('slip_status_id', '!=', $value)
                ->orWhereNull('slip_status_id');
        }
    }

    private function applyAgentSearch($query, $field, $operator, $value)
    {
        if ($operator === 'avec') {
            $query->where($field, $value);
        } else {
            $query->where($field, '!=', $value)
                ->orWhereNull($field);
        }
    }

    private function applyRecordSearch($query, $operator, $value)
    {
        if ($operator === 'avec') {
            $query->whereHas('records', function ($q) use ($value) {
                $q->where('code', 'like', "%$value%")
                    ->orWhere('name', 'like', "%$value%");
            });
        } else {
            $query->whereDoesntHave('records', function ($q) use ($value) {
                $q->where('code', 'like', "%$value%")
                    ->orWhere('name', 'like', "%$value%");
            });
        }
    }

    private function applyContainerSearch($query, $operator, $value)
    {
        if ($operator === 'avec') {
            $query->whereHas('records.containers', function ($q) use ($value) {
                $q->where('containers.id', $value);
            });
        } else {
            $query->whereDoesntHave('records.containers', function ($q) use ($value) {
                $q->where('containers.id', $value);
            });
        }
    }
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

