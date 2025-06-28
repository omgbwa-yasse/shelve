<?php

namespace App\Http\Controllers;

use App\Enums\CommunicationStatus;
use Illuminate\Http\Request;
use App\Models\communication;
use App\Models\Activity;
use App\Models\Building;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\floor;
use App\Models\Organisation;
use App\Models\User;
use App\Models\Container;
use App\Models\RecordStatus;
use App\Models\Term;
use App\Models\communicationRecord;


class SearchCommunicationController extends Controller
{

    public function form()
    {
        $statuses = collect(CommunicationStatus::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label()
            ];
        });

        $data = [
            'statuses' => $statuses,
            'operators' => User::select('id', 'name')->get(),
            'users' => User::select('id', 'name')->get(),
            'organisations' => Organisation::select('id', 'name')->get(),
        ];

        return view('search.communication.advanced', compact('data'));
    }

    public function advanced(Request $request)
    {
        $fields = $request->input('field');
        $operators = $request->input('operator');
        $values = $request->input('value');

        $query = Communication::query();

        if ($fields && $operators && $values) {
            foreach ($fields as $index => $field) {
                $operator = $operators[$index];
                $value = $values[$index];

                switch ($field) {
                    case 'code':
                    case 'name':
                    case 'content':
                        $this->applyTextSearch($query, $field, $operator, $value);
                        break;

                    case 'return_date':
                    case 'return_effective':
                        $this->applyDateSearch($query, $field, $operator, $value);
                        break;

                    case 'operator':
                        $this->applyUserSearch($query, 'operator_id', $operator, $value);
                        break;

                    case 'user':
                        $this->applyUserSearch($query, 'user_id', $operator, $value);
                        break;

                    case 'operator_organisation':
                        $this->applyOrganisationSearch($query, 'operator_organisation_id', $operator, $value);
                        break;

                    case 'user_organisation':
                        $this->applyOrganisationSearch($query, 'user_organisation_id', $operator, $value);
                        break;

                    case 'status':
                        $this->applyStatusSearch($query, $operator, $value);
                        break;

                    case 'record':
                        $this->applyRecordSearch($query, $operator, $value);
                        break;
                }
            }
        }

        $communications = $query->with([
            'operator',
            'operatorOrganisation',
            'user',
            'userOrganisation',
            'status',
            'records.record'
        ])->paginate(20);

        return view('communications.index', compact('communications'));
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
            $query->where('status', $value);
        } else {
            $query->where('status', '!=', $value)
                ->orWhereNull('status');
        }
    }

    private function applyRecordSearch($query, $operator, $value)
    {
        if ($operator === 'avec') {
            $query->whereHas('records', function($q) use ($value) {
                $q->where('record_id', $value);
            });
        } else {
            $query->whereDoesntHave('records', function($q) use ($value) {
                $q->where('record_id', $value);
            });
        }
    }
    public function index(Request $request)
    {
        $communications = '';
        $title = '';

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

                $communications = $query->paginate(10);
                break;



            case "code":
                $communications = communication::where('code', $request->input('value'))
                    ->paginate(10);
                break;



            case "operator":
                $communications = communication::where('operator_id', $request->input('id'))
                    ->paginate(10);
                    $user=User::findOrFail($request->input('id'))->pluck('name');
                    $title = "de ". $user->name;
                break;



            case "operator-organisation":
                $communications = communication::where('operator_organisation_id', $request->input('id'))
                    ->paginate(10);
                    $organisation=Organisation::findOrFail($request->input('id'))->pluck('name');
                    $title = "de ". $organisation->name;
                break;


            case "user":
                $communications = communication::where('user_id', $request->input('id'))
                    ->paginate(10);
                    $user=User::findOrFail($request->input('id'))->pluck('name');
                    $title = "de ". $user->name;
                break;


            case "user-organisation":
                $communications = communication::where('user_organisation_id', $request->input('id'))
                    ->paginate(10);
                    $organisation=Organisation::findOrFail($request->input('id'))->pluck('name');
                    $title = "de ". $organisation->name;
                break;


            case "return-available":
                $communications = communication::where('return_date','>=', now()->format('Y-m-d'))
                    ->paginate(10);
                    $title = "date de retour non atteinte";
                break;


            case "not-return":
                    $communications = communication::where('return_effective', NULL)
                    ->paginate(10);
                    $title = "non returnées";
                    break;

            case "unreturn":
                $communications = communication::where('return_date', NULL)
                    ->paginate(10);
                    $title = "sans retour";
                break;


            case "return-effective":
                $communications = communication::where('return_effective', '<=', now())
                    ->paginate(10);
                    $title = "returnées";
                break;


            default:
                $communications = communication::take(5)->paginate(10);
                break;
        }


        return view('communications.index', compact('communications', 'title'));
    }


    public function date()
    {
        return view('search.communication.dateSearch');
    }

}

