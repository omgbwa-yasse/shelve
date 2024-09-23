<?php

namespace App\Http\Controllers;
use App\Models\Activity;
use App\Models\Communicability;
use Illuminate\Http\Request;

class activityCommunicabilityController extends Controller
{

    public function index($activityId)
    {
        $activity = Activity::findOrFail($activityId);
        $communicabilities = $activity->communicabilities;

        return view('activities.communicabilities.index', compact('activity', 'communicabilities'));
    }



    public function create($activityId)
    {
        $activity = Activity::findOrFail($activityId);
        $communicabilities = Communicability::all();

        return view('activities.communicabilities.create', compact('activity','communicabilities'));
    }


    public function store(Request $request, $activityId)
    {
        $activity = Activity::findOrFail($activityId);
        $communicability = Communicability::findOrFail($request->input('communicability_id'));
        $activity->communicability_id = $communicability->id;
        $activity->save();

        return redirect()->route('activities.communicabilities.index', $activityId);
    }



    public function edit($activityId, $communicabilityId)
    {
        $activity = Activity::findOrFail($activityId);
        $communicability = Communicability::findOrFail($communicabilityId);
        $communicabilities = Communicability::all();

        return view('activities.communicabilities.edit', compact('activity', 'communicabilities', 'communicability'));
    }




    public function update(Request $request, $activityId, $communicabilityId)
    {
        $activity = Activity::findOrFail($activityId);
        $communicability = Communicability::findOrFail($request->input('communicability_id'));
        $activity->communicability_id = $communicability->id;
        $activity->save();

        return redirect()->route('activities.communicabilities.index', $activityId);
    }






    public function destroy($activityId, $communicabilityId)
    {
        $activity = Activity::findOrFail($activityId);
        $communicability = Communicability::findOrFail($communicabilityId);
        $activity->communicabilities()->detach($communicability->id);

        return redirect()->route('activities.communicabilities.index', $activityId);
    }




    public function show($activityId, $communicabilityId)
    {
        $activity = Activity::findOrFail($activityId);
        $communicability = Communicability::findOrFail($communicabilityId);

        return view('activities.communicabilities.show', compact('activity', 'communicability'));
    }

}
