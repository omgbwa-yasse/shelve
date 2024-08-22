<?php

namespace App\Http\Controllers;
use App\Models\Mail;
use App\Models\Record;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\Author;
use App\Models\communicationRecord;
use App\Models\MailTransaction;
use App\Models\RecordStatus;
use App\Models\Term;
use App\Models\Slip;
use App\Models\SlipRecord;

use Illuminate\Http\Request;



class SearchMailFeedbackController extends Controller
{
    public function index(request $request){

        $query = $request->input('query');

        $mails = MailTransaction::with('action','type','documentType','mailType',
            'organisationReceived','userReceived','organisationSend','userSend','mail')->get();
        // revoir la liste
        return view('mails.transactions.index', compact('mails', 'priorities', 'types', 'typologies', 'authors'));
    }


}
