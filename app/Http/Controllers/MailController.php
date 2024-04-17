<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Mail;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailAttachment;

class MailController extends Controller
{


    public function index()
    {
        $mails = Mail::with('mailPriority', 'mailTypology', 'mailAttachment')->get();
        return view('mails.index', compact('mails'));
    }



    public function create()
    {
        $mailPriorities = MailPriority::all();
        $mailTypologies = MailTypology::all();
        return view('mails.create', compact('mailPriorities', 'mailTypologies'));
    }



    public function store(Request $request)
    {
        $mail = Mail::create($request->all());

        if ($request->hasFile('document')) {
            $document = $request->file('document');
            $path = $document->store('mail_attachments');
            $mailAttachment = MailAttachment::create([
                'path' => $path,
                'filename' => $document->getClientOriginalName(),
                'size' => $document->getSize(),
            ]);
            $mail->mailAttachment()->associate($mailAttachment);
        }

        return redirect()->route('mails.index')
                        ->with('success','Mail created successfully');
    }



    public function show(Mail $mail)
    {
        return view('mails.show', compact('mail'));
    }



    public function edit(Mail $mail)
    {
        $mailPriorities = MailPriority::all();
        $mailTypologies = MailTypology::all();
        return view('mails.edit', compact('mail', 'mailPriorities', 'mailTypologies'));
    }


    public function update(Request $request, Mail $mail)
    {
        $mail->update($request->all());

        if ($request->hasFile('document')) {
            $document = $request->file('document');
            $path = $document->store('mail_attachments');
            $mailAttachment = MailAttachment::create([
                'path' => $path,
                'filename' => $document->getClientOriginalName(),
                'size' => $document->getSize(),
            ]);
            $mail->mailAttachment()->associate($mailAttachment);
        }

        return redirect()->route('mails.index')
                        ->with('success','Mail updated successfully');
    }


    public function destroy(Mail $mail)
    {
        $mail->delete();
        return redirect()->route('mails.index')
                        ->with('success','Mail deleted successfully');
    }
}


