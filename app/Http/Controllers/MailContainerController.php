<?php

namespace App\Http\Controllers;

use App\Models\MailContainer;
use App\Models\ContainerType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MailContainerController extends Controller
{
    public function index()
    {
        $mailContainers = MailContainer::with(['containerType', 'creator', 'organisation']) // Removed 'mailArchivings'
                                      ->where('creator_organisation_id', auth()->user()->current_organisation_id) // Corrected field name
                                      ->paginate(10);
        dd($mailContainers);

        return view('mails.containers.index', compact('mailContainers'));
    }


    public function create()
    {
        $containerTypes = ContainerType::all();
        return view('mails.containers.create', compact('containerTypes')); // Removed 'mails'
    }



    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:mail_containers', // Added unique validation
            'name' => 'required',
            'type_id' => 'required|exists:container_types,id', // Added exists validation
        ]);

        MailContainer::create([
            'code' => $request->code,
            'name' => $request->name,
            'type_id' => $request->type_id,
            'created_by' => auth()->id(), // Corrected field name
            'creator_organisation_id' => auth()->user()->current_organisation_id,
        ]);

        return redirect()->route('mail-container.index')
                        ->with('success','Mail Container created successfully.');
    }



    public function show(int $id)
    {
        $mailContainer = MailContainer::with('containerType', 'creator')->findOrFail($id);
        return view('mails.containers.show', compact('mailContainer'));
    }




    public function edit(int $id)
    {
        $containerTypes = ContainerType::all();
        $mailContainer = MailContainer::with('containerType')->findOrFail($id);
        return view('mails.containers.edit', compact('mailContainer', 'containerTypes'));
    }



    public function update(Request $request, MailContainer $mailContainer)
    {
        $request->validate([
            'code' => 'required|unique:mail_containers,code,' . $mailContainer->id, // Added unique validation
            'name' => 'required',
            'type_id' => 'required|exists:container_types,id', // Added exists validation
        ]);

        $mailContainer->update([
            'code' => $request->code,
            'name' => $request->name,
            'type_id' => $request->type_id,
        ]);

        return redirect()->route('mail-container.index')
                        ->with('success','Mail Container updated successfully');
    }



    public function destroy(int $id)
    {
        $mailContainer = MailContainer::findOrFail($id);

        if ($mailContainer->mails->isEmpty()) { // Use the "mails" relationship to check for associated archives
            $mailContainer->delete();
            return redirect()->route('mail-container.index')
                            ->with('success', 'Mail Container deleted successfully.'); // Added success message
        } else {
            return redirect()->route('mail-container.index')
                            ->with('error', 'Mail Container cannot be deleted because it has associated mail archives.');
        }
    }


    public function getContainers()
    {
        $containers = MailContainer::select('id', 'code', 'name')
            ->whereHas('organisation',function($query){
                $query->where('id', Auth::user()->current_organisation_id);
            })
            ->get();
        return response()->json($containers);
    }




}
