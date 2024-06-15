<?php

namespace App\Http\Controllers;

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
        return view('mails.received.index', compact('transactions'));
    }



    public function create()
    {
        $type = mailType::where('name','=','receveid');
        $mails = mail::all();
        $users = user::all();
        $organisations = organisation ::all();
        $mailStatuses = MailStatus:: all();
        return view('mails.received.create', compact('mails','users','organisations','mailStatuses'));
    }



    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|integer',
            'mail_id' => 'required|exists:mails,id',
            'user_send_id' => 'required|exists:users,id',
            'organisation_send_id' => 'required|exists:organisations,id',
            'user_received_id' => 'required|exists:users,id',
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




    public function show(MailTransaction $MailTransaction)
    {
        return view('mails.received.show', compact('MailTransaction'));
    }



    public function edit(MailTransaction $MailTransaction)
    {
        return view('mails.received.edit', compact('MailTransaction'));
    }



    public function update(Request $request, MailTransaction $MailTransaction)
    {
        $request->validate([
            'code' => 'required|integer',
            'date_creation' => 'required|date',
            'mail_id' => 'required|exists:mails,id',
            'user_send' => 'required|exists:users,id',
            'organisation_send_id' => 'required|exists:organisations,id',
            'user_received' => 'nullable|exists:users,id',
            'organisation_received_id' => 'nullable|exists:organisations,id',
            'mail_status_id' => 'required|exists:mail_status,id',
        ]);

        $MailTransaction->update($request->all());

        return redirect()->route('mails.received.index')
            ->with('success', 'MailTransaction updated successfully');
    }



    public function destroy(MailTransaction $MailTransaction)
    {
        $MailTransaction->delete();

        return redirect()->route('mails.received.index')
            ->with('success', 'MailTransaction deleted successfully');
    }
}

