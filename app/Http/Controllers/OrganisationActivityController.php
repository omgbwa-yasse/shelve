<?php

namespace App\Http\Controllers;

use App\Models\OrganisationActivity;
use App\Models\Organisation;
use App\Models\Activity;
use Illuminate\Http\Request;


class OrganisationActivityController extends Controller
{





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
        return redirect()->route('organisations.index', $organisation);
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

        return redirect()->route('organisations.show', $organisation);
    }



    public function destroy(Organisation $organisation, Activity $activity)
    {
        OrganisationActivity::where(['organisation_id' => $organisation->id, 'activity_id' => $activity->id])->delete();
        return redirect()->route('organisations.show', $organisation);
    }
}


