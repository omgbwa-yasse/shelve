<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use App\Models\Activity;
use App\Models\Author;
use App\Models\Building;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Floor;
use App\Models\Container;
use App\Models\RecordStatus;

use App\Models\SlipStatus;
use App\Models\Organisation;
use App\Models\RecordSupport;
use App\Models\User;
use App\Models\RecordLevel;
use App\Models\ThesaurusConcept;
use Illuminate\Support\Facades\Auth;

class SearchRecordController extends Controller
{
    public function form()
    {
        $data = [
            'rooms' => Room::select('id', 'name', 'code')->get(),
            'shelve' => Shelf::select('id', 'code')->get(),
            'activities' => Activity::select('id', 'name')->get(),
            'terms' => ThesaurusConcept::with('labels')->get()->map(function($concept) {
                return [
                    'id' => $concept->id,
                    'name' => $concept->preferred_label
                ];
            }),
            'authors' => Author::select('id', 'name')->get(),
            'creators' => User::select('id', 'name')->get(),
            'statues' => RecordStatus::select('id', 'name')->get(),
            'containers' => Container::select('id', 'code')->get(),
        ];

        return view('search.record.advanced', compact('data'));
    }

    public function advanced(Request $request)
    {
        $fields = $request->input('field');
        $operators = $request->input('operator');
        $values = $request->input('value');

        $query = Record::query();

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

                    case 'date_start':
                    case 'date_end':
                    case 'date_exact':
                    case 'date_creation':
                    case 'dua':
                    case 'dul':
                        $this->applyDateSearch($query, $field, $operator, $value);
                        break;

                    case 'room':
                    case 'shelf':
                    case 'activity':
                    case 'term':
                    case 'author':
                    case 'creator':
                    case 'container':
                    case 'status':
                        $this->applyRelationSearch($query, $field, $operator, $value);
                        break;

                    default:
                        $query->where($field, '=', $value);
                        break;
                }
            }
        }

        // Chargement des données nécessaires pour la vue
        $records = $query->with([
            'status',
            'support',
            'level',
            'activity',
            'containers',
            'user',
            'authors',
            'thesaurusConcepts'
        ])->paginate(20);

        // Données additionnelles pour la vue
        $viewData = [
            'records' => $records,
            'statuses' => RecordStatus::all(),
            'slipStatuses' => SlipStatus::all(),
            'terms' => ThesaurusConcept::with('labels')->get(),
            'users' => User::select('id', 'name')->get(),
            'organisations' => Organisation::select('id', 'name')->get(),
        ];

        return view('records.index', $viewData);
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

    private function applyRelationSearch($query, $field, $operator, $value)
    {
        switch ($field) {
            case 'room':
                if ($operator === 'avec') {
                    $query->whereHas('containers.shelf.room', function ($q) use ($value) {
                        $q->where('rooms.id', $value);
                    });
                } else {
                    $query->whereDoesntHave('containers.shelf.room', function ($q) use ($value) {
                        $q->where('rooms.id', $value);
                    });
                }
                break;

            case 'shelf':
                if ($operator === 'avec') {
                    $query->whereHas('containers.shelf', function ($q) use ($value) {
                        $q->where('shelves.id', $value);
                    });
                } else {
                    $query->whereDoesntHave('containers.shelf', function ($q) use ($value) {
                        $q->where('shelves.id', $value);
                    });
                }
                break;

            case 'container':
                if ($operator === 'avec') {
                    $query->whereHas('containers', function ($q) use ($value) {
                        $q->where('containers.id', $value);
                    });
                } else {
                    $query->whereDoesntHave('containers', function ($q) use ($value) {
                        $q->where('containers.id', $value);
                    });
                }
                break;

            case 'creator':
                $query->where('user_id', $operator === 'avec' ? '=' : '!=', $value);
                break;

            default:
                $relation = $this->getRelationName($field);
                if ($operator === 'avec') {
                    $query->whereHas($relation, function ($q) use ($value) {
                        $q->where('id', $value);
                    });
                } else {
                    $query->whereDoesntHave($relation, function ($q) use ($value) {
                        $q->where('id', $value);
                    });
                }
                break;
        }
    }

    private function getRelationName($field)
    {
        $relationMap = [
            'activity' => 'activity',
            'term' => 'thesaurusConcepts',
            'author' => 'authors',
            'creator' => 'user',
            'status' => 'status'
        ];

        return $relationMap[$field] ?? $field;
    }

    public function sort(Request $request)
    {
        $query = Record::query();

        switch ($request->input('categ')) {
            case "dates":
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');

                if ($exactDate) {
                    $query->whereDate('date_exact', $exactDate);
                }

                if ($startDate && $endDate) {
                    $query->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->whereDate('date_start', '>=', $startDate)
                            ->whereDate('date_end', '<=', $endDate);
                    });
                }
                break;

            case "term":
            case "concept": // Ajout du cas "concept" pour prendre en charge les deux formats
                $conceptId = $request->input('id');
                $query->whereHas('thesaurusConcepts', function ($q) use ($conceptId) {
                    $q->where('thesaurus_concepts.id', $conceptId);
                });
                break;

            case "author":
                $authorId = $request->input('id');
                $query->whereHas('authors', function ($q) use ($authorId) {
                    $q->where('authors.id', $authorId);
                });
                break;

            case "activity":
                $activityId = $request->input('id');
                $query->where('activity_id', $activityId);
                break;

            case "container":
                $containerId = $request->input('id');
                $query->whereHas('containers', function ($q) use ($containerId) {
                    $q->where('containers.id', $containerId);
                });
                break;
        }

                $query->with([
            'level',
            'status',
            'support',
            'activity',
            'containers',
            'user',
            'authors',
            'thesaurusConcepts'
        ]);

        $records = $query->paginate(10);

        $viewData = [
            'records' => $records,
            'statuses' => RecordStatus::all(),
            'slipStatuses' => SlipStatus::all(),
            'terms' => ThesaurusConcept::with('labels')->get(),
            'users' => User::select('id', 'name')->get(),
            'organisations' => Organisation::select('id', 'name')->get(),
            'supports' => RecordSupport::all(),
            'activities' => Activity::all(),
            'containers' => Container::all(),
            'levels' => RecordLevel::all(),
            'authors' => Author::with('type')->get()
        ];

        return view('records.index', $viewData);
    }

    public function selectLast()
    {
        $records = Record::with([
            'status',
            'support',
            'level',
            'activity',
            'containers',
            'user',
            'authors',
            'thesaurusConcepts'
        ])
        ->whereHas('activity.organisations', function($query) {
            $query->where('organisations.id', Auth::user()->current_organisation_id);
        })
        ->latest()
        ->paginate(10);

        $viewData = [
            'records' => $records,
            'statuses' => RecordStatus::all(),
            'slipStatuses' => SlipStatus::all(),
            'terms' => ThesaurusConcept::with('labels')->get(),
            'users' => User::select('id', 'name')->get(),
            'organisations' => Organisation::select('id', 'name')->get()
        ];

        return view('records.index', $viewData);
    }

    public function selectBuilding()
    {
        return view('search.record.buildingSearch', [
            'buildings' => Building::all()
        ]);
    }

    public function selectFloor(Request $request)
    {
        return view('search.record.floorSearch', [
            'floors' => Floor::where('building_id', $request->input('id'))->get()
        ]);
    }

    public function selectRoom(Request $request)
    {
        return view('search.record.roomSearch', [
            'rooms' => Room::where('floor_id', $request->input('id'))->get()
        ]);
    }

    public function selectShelve(Request $request)
    {
        return view('search.record.shelveSearch', [
            'shelves' => Shelf::where('room_id', $request->input('id'))->get()
        ]);
    }

    public function selectContainer(Request $request)
    {
        return view('search.record.containerSearch', [
            'containers' => Container::where('shelve_id', $request->input('id'))->get()
        ]);
    }

    public function selectWord()
    {
        return view('search.record.wordSearch', [
            'terms' => ThesaurusConcept::with('labels')->paginate(50)
        ]);
    }

    public function selectActivity()
    {
        return view('search.record.activitySearch', [
            'activities' => Activity::all()
        ]);
    }

    public function date()
    {
        return view('search.record.dateSearch');
    }
}
