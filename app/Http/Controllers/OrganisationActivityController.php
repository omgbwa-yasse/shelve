<?php

namespace App\Http\Controllers;

use App\Models\OrganisationActivity;
use App\Models\Organisation;
use App\Models\Activity;
use Illuminate\Http\Request;


class OrganisationActivityController extends Controller
{


    public function index(Organisation $organisation)
    {
        $activities = $organisation->activities;

        return view('organisations.activities.index', compact('activities'));
    }



    public function create(Organisation $organisation)
    {
        $availableActivities = Activity::all();
        return view('organisations.activities.create', compact('organisation', 'availableActivities'));
    }




    public function store(Request $request, Organisation $organisation)
    {
        $request->validate([
            'activity_id' => 'required|exists:activities,id',
        ]);

        organisationActivity::create([
            'organisation_id' => $organisation->id,
            'activity_id' => $request->activity_id,
            'creator_id' => auth()->id(),
        ]);

        $organisation = Organisation::findOrFail($organisation->id);
        return redirect()->route('organisations.activities.index', $organisation);
    }





    public function show(Organisation $organisation, OrganisationActivity $organisationActivity)
    {
        return view('organisations.activities.show', compact('organisationActivity'));
    }





    public function edit(Organisation $organisation, OrganisationActivity $organisationActivity)
    {

        $availableActivities = Activity::all();
        return view('organisations.activities.edit', compact('organisationActivity', 'availableActivities'));
    }





    public function update(Request $request, Organisation $organisation, OrganisationActivity $organisationActivity)
    {
        $request->validate([
            'activity_id' => 'required|exists:activities,id',
        ]);

        $organisationActivity->update([
            'activity_id' => $request->activity_id,
        ]);

        return redirect()->route('organisations.activities.show', [$organisation, $organisationActivity]);
    }


    public function destroy(Organisation $organisation, OrganisationActivity $organisationActivity)
    {
        $organisationActivity->delete();
        return redirect()->route('organisations.activities.index', $organisation);
    }
}


