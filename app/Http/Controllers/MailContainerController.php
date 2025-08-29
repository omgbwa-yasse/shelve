<?php

namespace App\Http\Controllers;

use App\Models\MailContainer;
use App\Models\ContainerProperty;
use App\Models\Activity;
use App\Models\Organisation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MailContainerController extends Controller
{
    public function index()
    {
        $mailContainers = MailContainer::with(['containerProperty', 'creator', 'organisation']) // Changed containerType to containerProperty
                                      ->where('creator_organisation_id', Auth::user()->current_organisation_id) // Corrected field name
                                      ->paginate(10);

        // Data for transfer modal
        $activities = Activity::where('organisation_id', Auth::user()->current_organisation_id)
                             ->orderBy('name')
                             ->get();

        $services = Organisation::where('id', '!=', Auth::user()->current_organisation_id)
                                ->orderBy('name')
                                ->get();

        return view('mails.containers.index', compact('mailContainers', 'activities', 'services'));
    }


    public function create()
    {
        $containerProperties = ContainerProperty::all();
        return view('mails.containers.create', compact('containerProperties')); // Changed containerTypes to containerProperties
    }



    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:mail_containers', // Added unique validation
            'name' => 'required',
            'property_id' => 'required|exists:container_properties,id', // Changed type_id to property_id and container_types to container_properties
        ]);

        MailContainer::create([
            'code' => $request->code,
            'name' => $request->name,
            'property_id' => $request->property_id, // Changed type_id to property_id
            'created_by' => Auth::id(), // Corrected field name
            'creator_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return redirect()->route('mail-container.index')
                        ->with('success','Mail Container created successfully.');
    }



    public function show(int $id)
    {
        $mailContainer = MailContainer::with('containerProperty', 'creator')->findOrFail($id); // Changed containerType to containerProperty
        return view('mails.containers.show', compact('mailContainer'));
    }




    public function edit(int $id)
    {
        $containerProperties = ContainerProperty::all(); // Changed containerTypes to containerProperties
        $mailContainer = MailContainer::with('containerProperty')->findOrFail($id); // Changed containerType to containerProperty
        return view('mails.containers.edit', compact('mailContainer', 'containerProperties')); // Changed containerTypes to containerProperties
    }



    public function update(Request $request, MailContainer $mailContainer)
    {
        $request->validate([
            'code' => 'required|unique:mail_containers,code,' . $mailContainer->id, // Added unique validation
            'name' => 'required',
            'property_id' => 'required|exists:container_properties,id', // Changed type_id to property_id and container_types to container_properties
        ]);

        $mailContainer->update([
            'code' => $request->code,
            'name' => $request->name,
            'property_id' => $request->property_id, // Changed type_id to property_id
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


    public function getContainerProperties()
    {
        $properties = ContainerProperty::select('id', 'name')->get();
        return response()->json($properties);
    }

}
