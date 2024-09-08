<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;
use App\Models\Record;
use App\Models\RecordSupport;
use App\Models\RecordStatus;
use App\Models\Container;
use App\Models\Activity;
use App\Models\Term;
use App\Models\Accession;
use App\Models\Author;
use App\Models\RecordLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class lifeCycleController extends Controller
{

    public function recordToRetain(){
        $records = Record::all();
        $title = "actifs";
        return view('records.index', compact('records','title'));
    }


    public function recordToTransfer(){
        $records = Record::all();
        $title = "à transferer";
        return view('records.index', compact('records','title'));
    }


    public function recordToSort(){
        $records = Record::all();
        $title = "à trier";
        return view('records.index', compact('records','title'));
    }



    public function recordToKeep(){
        $records = Record::all();
        $title = "à conserver";
        return view('records.index', compact('records','title'));
    }


    public function recordToEliminate(){
        $records = Record::all();
        $title = "à éliminer";
        return view('records.index', compact('records','title'));
    }


}
