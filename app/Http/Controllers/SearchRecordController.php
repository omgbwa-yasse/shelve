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

class SearchRecordController extends Controller
{
    public function index(Request $request)
    {   $records = '';
        switch($request->input('categ')){
            case "dates":
                $exactDate = $request->input('date_exact');
                $startDate = $request->input('date_start');
                $endDate = $request->input('date_end');


                $query = record::query();

                if ($exactDate) {
                    $query->whereDate('date_exact', $exactDate);
                }

                if ($startDate && $endDate) {
                    $query->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('date_start', '>=', $startDate)
                            ->whereDate('date_end', '<=', $endDate);
                    });
                }

                $records = $query->get();
                break;

            case "typology":
                $records = record::where('record_typology_id', $request->input('id'))
                    ->get();
                break;

            case "term":
                $id = $request->input('id');
                $records = Record::whereHas('terms', function ($query) use ($id) {
                    $query->where('id', $id);
                })->get();
                break;

            case "author":
                $records = Record::join('record_author', 'records.id', '=', 'record_author.record_id')
                    ->where('record_author.author_id', $request->input('id'))
                    ->get();
                break;

            case "activity":
                $records = record::where('activity_id', $request->input('id'))
                    ->get();
                break;


            case "container":
                $records = Record::where('container_id',  $request->input('id'))->get();
                break;

            default:
                $records = record::take(5)->get();
                break;
        }

        $statuses = RecordStatus::all();
        $terms = Term::all();
        return view('records.index', compact('records','terms', 'statuses'));
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
        $records = Record::with(['level', 'status', 'support', 'activity', 'parent', 'container', 'user', 'authors', 'terms'])
            ->latest()
            ->paginate(10);

        $statuses = RecordStatus::all();
        $terms = Term::all();

        return view('search.record.lastSearch', compact('records', 'statuses', 'terms'));
    }






}

