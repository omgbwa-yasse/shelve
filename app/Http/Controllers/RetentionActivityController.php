<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\RetentionActivity;
use App\Models\Activity;
use App\Models\Retention;

class RetentionActivityController extends Controller
{
    public function index($activityId)
    {
        $activity = Activity::findOrFail($activityId);
        $retentions = $activity::with('retention')->get();
        return view('activities.retentions.index', compact('activity', 'retentions'));
    }



    public function create($activityId)
    {
        $activity = Activity::findOrFail($activityId);
        $retentions = Retention::all();
        return view('activities.retentions.create', compact('activity', 'retentions'));
    }




    public function store(Request $request, $activityId)
    {
        $request->validate([
            'retention_id' => 'required|exists:retentions,id',
        ]);

        $activity = Activity::findOrFail($activityId);
        $retentionActivity = new RetentionActivity([
            'retention_id' => $request->input('retention_id'),
            'activity_id' => $activity->id,
        ]);

        $retentionActivity->save();

        return redirect()->route('activities.retentions.index', $activityId)->with('success', 'Retention added successfully.');
    }




    public function edit($activityId, $retentionActivityId)
    {
        $activity = Activity::findOrFail($activityId);
        $retentionActivity = RetentionActivity::findOrFail($retentionActivityId);
        $retentions = Retention::all();
        return view('activities.retentions.edit', compact('activity', 'retentionActivity', 'retentions'));
    }




    public function update(Request $request, $activityId, $retentionActivityId)
    {
        $request->validate([
            'retention_id' => 'required|exists:retentions,id',
        ]);

        $retentionActivity = RetentionActivity::findOrFail($retentionActivityId);
        $retentionActivity->retention_id = $request->input('retention_id');
        $retentionActivity->save();

        return redirect()->route('activities.retentions.index', $activityId)->with('success', 'Retention updated successfully.');
    }



    public function destroy($activityId, $retentionActivityId)
    {
        $retentionActivity = RetentionActivity::findOrFail($retentionActivityId);
        $retentionActivity->delete();

        return redirect()->route('activities.retentions.index', $activityId)->with('success', 'Retention deleted successfully.');
    }

}
