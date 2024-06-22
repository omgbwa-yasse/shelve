<?php

namespace App\Http\Controllers;
use App\Models\MailContainer;
use App\Models\ContainerType;
use App\Models\Mail;
use App\Models\MailArchiving;
use Illuminate\Http\Request;

class MailContainerController extends Controller
{

    public function index()
    {
        $mailContainers = MailContainer::all();
        $mailContainers->load('containerType','creator','mailArchivings');
        return view('mails.containers.index', compact('mailContainers'));
    }




    public function create()
    {
        $containerTypes = ContainerType::all();
        $mails = Mail::all();
        return view('mails.containers.create', compact('containerTypes', 'mails'));
    }




    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'name' => 'required',
            'type_id' => 'required'
        ]);

        $request->merge(['user_id' => auth()->id()]);

        MailContainer::create($request->all());

        return redirect()->route('mail-container.index')
                        ->with('success','Mail Container created successfully.');
    }




    public function show(int $id)
    {
        $mailContainer = MailContainer::with('containerType','creator')->findOrFail($id);
        return view('mails.containers.show', compact('mailContainer'));
    }




    public function edit(INT $id)
    {
        $containerTypes = ContainerType::all();
        $mailContainer = mailContainer::with('containerType')->findOrFail($id);
        return view('mails.containers.edit', compact('mailContainer', 'containerTypes'));
    }





    public function update(Request $request, MailContainer $mailContainer)
    {
        $request->validate([
            'code' => 'required',
            'name' => 'required',
            'type_id' => 'required'
        ]);

        $request->merge(['user_id' => auth()->id()]);

        $mailContainer->update($request->all());

        return redirect()->route('mail-container.index')
                        ->with('success','Mail Container updated successfully');
    }



    public function destroy(INT $id)
    {
        $mailContainer = mailContainer::with('containerType', 'mailArchivings')->findOrFail($id);
        if ($mailContainer->mailArchivings->isEmpty()) {
            $mailContainer->delete();
            return redirect()->route('mail-container.index');
        } else {
            return redirect()->route('mail-container.index')
                            ->with('error', 'Mail Container cannot be deleted because it has associated mail archivings.');
        }
    }

}


