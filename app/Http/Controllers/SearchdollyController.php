<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\dolly;
use App\Models\Record;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\Author;
use App\Models\BatchMail;
use App\Models\MailArchiving;
use App\Models\MailContainer;
use App\Models\RecordStatus;
use App\Models\Term;
use App\Models\Slip;
use App\Models\SlipRecord;

class SearchdollyController extends Controller
{
    public function index(Request $request)
    {
        $dollies = [];

        switch ($request->input('categ')) {
            case "record":
            case "communication":
            case "transferring":
            case "building":
            case "shelf":
            case "slip":
            case "mail":
            case "room":
            case "slip_record":
            case "container":
                $dollies = Dolly::where('category', $request->input('categ'))->get();
                break;

            default:
                $dollies = Dolly::take(5)->get();
                break;
        }

        return view('dollies.index', compact('dollies'));
    }
}


