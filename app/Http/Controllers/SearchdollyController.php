<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\dolly;
use App\Models\RecordPhysical;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\Author;
use App\Models\BatchMail;
use App\Models\MailArchiving;
use App\Models\MailContainer;
use App\Models\RecordStatus;

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
            case "digital_folder":
            case "digital_document":
            case "book":
            case "book_series":
                $dollies = Dolly::where('category', $request->input('categ'))->paginate(25);
                break;

            default:
                $dollies = Dolly::paginate(25);
                break;
        }

        return view('dollies.index', compact('dollies'));
    }
}


