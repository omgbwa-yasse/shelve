<?php

namespace App\Http\Controllers;

use App\Models\documentType;
use App\Models\Mail;
use App\Models\User;
use App\Models\MailType;
use App\Models\MailStatus;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\UserOrganisation;
use Illuminate\Http\Request;
use App\Models\MailAttachment;
use App\Models\MailTransaction;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class MailReceivedController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $transactions = MailTransaction::where('user_received_id', $user->id)->get();
        $transactions->load('mails');
        $transactions->load('organisationSend');
        $transactions->load('organisationReceived');
        return view('mails.received.index', compact('transactions'));
    }



    public function create()
    {
        $type = mailType::where('name','=','received');
        $mails = mail::all();
        $users = User::where('id', '!=', auth()->id())->get();
        $organisations = organisation ::all();
        $mailStatuses = MailStatus:: all();
        $documentTypes = documentType :: all();
        return view('mails.received.create', compact('mails','users','organisations','mailStatuses','documentTypes'));
    }



    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|integer',
            'mail_id' => 'required|exists:mails,id',
            'user_send_id' => 'required|exists:users,id',
            'organisation_send_id' => 'required|exists:organisations,id',
            'user_received_id' => 'required|exists:users,id',
            'document_type_id' => 'required|exists:document_types,id',
        ]);

        $validatedData['date_creation'] = now();
        $userOrganisations = UserOrganisation::where('user_id', '=', $validatedData['user_received_id'])->get();

        foreach ($userOrganisations as $userOrganisation) {
            if ($userOrganisation->active) {
                $validatedData['organisation_received_id'] = $userOrganisation->organisation_id;
                break;
            }
        }

        $validatedData['mail_type_id'] = 1; // 1 Recevoir et 2 Emettre

        MailTransaction::create($validatedData);

        return redirect()->route('mail-received.index')
            ->with('success', 'MailTransaction created successfully.');
    }




    public function show(int $id)
    {
        $mailTransaction = MailTransaction::with([
            'mails',
            'documentType',
            'mailStatus',
            'userReceived',
            'userSend',
            'organisationReceived',
            'organisationSend'
        ])->findOrFail($id);

        return view('mails.received.show', compact('mailTransaction'));
    }



    public function edit(MailTransaction $MailTransaction)
    {
        return view('mails.received.edit', compact('MailTransaction'));
    }



    public function update(Request $request, MailTransaction $mailTransaction)
    {
        $validatedData = $request->validate([
            'code' => 'required|integer',
            'date_creation' => 'required|date',
            'mail_id' => 'required|exists:mails,id',
            'user_send_id' => 'required|exists:users,id',
            'organisation_send_id' => 'required|exists:organisations,id',
            'user_received_id' => 'nullable|exists:users,id',
            'organisation_received_id' => 'nullable|exists:organisations,id',
            'mail_status_id' => 'required|exists:mail_status,id',
        ]);


        if ($validatedData['user_received_id'] !== null && $mailTransaction->user_received_id === null) {
            $validatedData['user_received_id'] = Auth::id();
            $validatedData['organisation_received_id'] = Auth::user()->organisation_id;
        }


        $mailTransaction->update($validatedData);

        return redirect()->route('mails.received.index')
            ->with('success', 'Mail Transaction updated successfully');
    }



    public function destroy($id)
    {
        $mailTransaction = MailTransaction::findOrFail($id);
        $mailTransaction->delete();
        return redirect()->route('mail-received.index')->with('success', 'MailTransaction deleted successfully');
    }
}

