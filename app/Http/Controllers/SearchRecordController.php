<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use App\Models\Activity;
use App\Models\recordPriority;
use App\Models\recordTypology;
use App\Models\recordType;
use App\Models\Author;
use App\Models\Batchrecord;
use App\Models\Building;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\floor;
use App\Models\Container;
use App\Models\recordArchiving;
use App\Models\recordContainer;
use App\Models\RecordStatus;
use App\Models\Term;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Exports\RecordsExport;
use App\Imports\RecordsImport;
use App\Models\SlipStatus;
use Illuminate\Support\Facades\Gate;
use App\Models\Attachment;
use App\Models\Dolly;
use App\Models\Organisation;
use App\Models\RecordSupport;
use App\Models\User;
use App\Models\Accession;
use App\Models\RecordLevel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SearchRecordController extends Controller
{

    public function index(Request $request)
    {
        $records = Record::query(); // Initialisation de la requête de base

        switch ($request->input('categ')) {
            case "dates":
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');

                if ($exactDate) {
                    $records->whereDate('date_exact', $exactDate);
                }

                if ($startDate && $endDate) {
                    $records->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('date_start', '>=', $startDate)
                              ->whereDate('date_end', '<=', $endDate);
                    });
                }
                break;

            case "typology":
                $typologyId = $request->input('id');
                $records->where('record_typology_id', $typologyId);
                break;

            case "term":
                $termId = $request->input('id');
                $records->whereHas('terms', function ($query) use ($termId) {
                    $query->where('id', $termId);
                });
                break;

            case "author":
                $authorId = $request->input('id');
                $records->join('record_author', 'records.id', '=', 'record_author.record_id')
                        ->where('record_author.author_id', $authorId);
                break;

            case "activity":
                $activityId = $request->input('id');
                $records->where('activity_id', $activityId);
                break;

            case "container":
                $containerId = $request->input('id');
                $records->join('record_container', 'records.id', '=', 'record_container.record_id')
                        ->where('record_container.container_id', $containerId);
                break;

            default:
                $records->take(5); // Limite de résultats en cas de catégorie non définie
                break;
        }

        $records = $records->paginate(10);

        // Récupération des données annexes pour la vue
        $statuses = RecordStatus::all();
        $terms = Term::all();
        $supports = RecordSupport::all();
        $activities = Activity::all();
        $containers = Container::all();
        $organisations = Organisation::all();
        $slipStatuses = SlipStatus::all();
        $users = User::all();
        $levels = RecordLevel::all();
        $authors = Author::with('authorType')->get();

        return view('records.index', compact('slipStatuses','organisations','users', 'records', 'terms', 'statuses', 'supports', 'activities', 'containers', 'levels', 'authors'));
    }




    public function date()
    {
        return view('search.record.dateSearch');
    }


    public function selectWord()
    {
        $terms = Term::all();
        $terms->load('parent','children','language','category','records','equivalentType','type');
        return view('search.record.wordSearch', compact('terms'));
    }


    public function selectActivity()
    {
        $activities = activity::all();
        return view('search.record.activitySearch', compact('activities'));
    }

    public function selectBuilding()
    {
        $buildings = Building::all();
        return view('search.record.buildingSearch', compact('buildings'));
    }

    public function selectRoom(Request $request)
    {
        $id = $request->input('id');
        $rooms = Room::where('floor_id', $id)->get();
        return view('search.record.roomSearch', compact('rooms'));
    }

    public function selectFloor(Request $request)
    {
        $id = $request->input('id');
        $floors = Floor::where('building_id', $id)->get();
        return view('search.record.floorSearch', compact('floors'));
    }

    public function selectShelve(Request $request)
    {
        $id = $request->input('id');
        $shelves = shelf::where('room_id', $id)->get();
        return view('search.record.shelveSearch', compact('shelves'));
    }

    public function selectContainer(Request $request)
    {
        $id = $request->input('id');
        $containers = container::where('shelve_id', $id)->get();
        return view('search.record.containerSearch', compact('containers'));
    }


    public function selectLast()
    {
        $records = Record::with(['level', 'status', 'support', 'activity', 'parent', 'containers', 'user', 'authors', 'terms'])
            ->latest()
            ->paginate(10);

        $statuses = RecordStatus::all();
        $terms = Term::all();
        $statuses = RecordStatus::all();
        $terms = Term::all();
        $users = User::select('id', 'name')->get();
        $slipStatuses = SlipStatus::all();
        $organisations = Organisation::select('id', 'name')->get();

        return view('records.index', compact(
            'records',
            'statuses',
            'slipStatuses',
            'terms',
            'users',
            'organisations'
        ));
    }






}

