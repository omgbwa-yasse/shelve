<?php

namespace App\Http\Controllers;
use App\Models\MailArchiving;
use Illuminate\Http\Request;
use App\Models\MailContainer;
use App\Models\DocumentType;
use App\Models\Mail;
use Illuminate\Support\Facades\Auth;


class MailArchivingController extends Controller
{


    public function index($id)
    {
        $mailArchivings = MailArchiving::with('container', 'mail', 'documentType')->where('container_id','=',$id);
        return view('mails.archiving.index', compact('mailArchivings'));
    }

    public function show(INT $id)
    {
        $mailArchiving = MailArchiving::with('container')->findOrFail('id');
        dd($mailArchiving);
        return view('mails.archiving.show', compact('mailArchiving'));
    }


    public function create()
{
    $mailContainers = MailContainer::all();

    $mails = Mail::where('create_by', Auth::id())
             ->whereHas('transactions', function ($query) {
                 $query->where('is_archived', false)
                       ->where('organisation_send_id', Auth::user()->current_organisation_id)
                       ->where('organisation_received_id', Auth::user()->current_organisation_id);
             })
             ->get();

    $documentTypes = DocumentType::all();

    return view('mails.archiving.create', compact('mailContainers', 'mails', 'documentTypes'));
}


    public function store(Request $request)
    {
        $request->validate([
            'container_id' => 'required|exists:mail_containers,id',
            'mail_id' => 'required|exists:mails,id',
            'document_type_id' => 'required|exists:document_types,id',
        ]);

        MailArchiving::create($request->all());

        return redirect()->route('mail-container.index')->with('success', 'Mail archiving created successfully.');
    }




    public function edit(MailArchiving $mailArchiving)
    {
        $mailContainers = MailContainer::all();
        $mails = Mail::all();
        $documentTypes = DocumentType::all();
        return view('mails.archiving.edit', compact('mailArchiving', 'mailContainers', 'mails', 'documentTypes'));
    }



    public function update(Request $request, MailArchiving $mailArchiving)
    {
        $request->validate([
            'container_id' => 'required|exists:mail_containers,id',
            'mail_id' => 'required|exists:mails,id',
            'document_type_id' => 'required|exists:document_types,id',
        ]);

        $mailArchiving->update($request->all());

        return redirect()->route('mail-archiving.index')->with('success', 'Mail archiving updated successfully.');
    }




    public function destroy(MailArchiving $mailArchiving)
    {
        if ($mailArchiving->container_id === null && $mailArchiving->mail_id === null && $mailArchiving->document_type_id === null) {
            $mailArchiving->delete();
            return redirect()->route('mail-archiving.index')->with('success', 'Mail archiving deleted successfully.');
        } else {
            return redirect()->route('mail-archiving.index')->with('error', 'Mail archiving cannot be deleted because it is not empty.');
        }
    }




}
