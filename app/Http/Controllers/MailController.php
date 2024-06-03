<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Mail;
use App\Models\MailType;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailAttachment;

class MailController extends Controller
{


    public function index()
    {
        $mails = Mail::with('mailPriority', 'mailTypology', 'mailAttachment')->get();
        return view('mails.received.index', compact('mails'));
    }


}


