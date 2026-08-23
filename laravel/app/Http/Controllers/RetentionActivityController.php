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
        $activity = Activity::with('retentions')->findOrFail($activityId);
        return view('activities.retentions.index', compact('activity'));
    }



    public function create($activityId)
    {
        $activity = Activity::with('retentions')->findOrFail($activityId);
        $retentions = Retention::all();
        return view('activities.retentions.create', compact('activity', 'retentions'));
    }




    public function store(Request $request, $activityId)
    {
        $request->validate([
            'retention_id' => 'required|exists:retentions,id',
        ]);

        $activity = Activity::findOrFail($activityId);
        // Une classe ne peut avoir qu'une règle effective. Remplacer la liaison
        // évite les sorts finaux contradictoires dans les listes du cycle de vie.
        $activity->retentions()->sync([$request->integer('retention_id')]);

        return redirect()->route('activities.show', $activityId)->with('success', 'Règle de conservation appliquée.');
    }




    public function edit($activityId, $retentionActivityId)
    {
        $activity = Activity::findOrFail($activityId);
        $retentionActivity = RetentionActivity::query()
            ->where('activity_id', $activityId)
            ->where('retention_id', $retentionActivityId)
            ->firstOrFail();
        $retentions = Retention::all();
        return view('activities.retentions.edit', compact('activity', 'retentionActivity', 'retentions'));
    }




    public function update(Request $request, $activityId, $retentionActivityId)
    {
        $request->validate([
            'retention_id' => 'required|exists:retentions,id',
        ]);

        $activity = Activity::findOrFail($activityId);
        $activity->retentions()->sync([$request->integer('retention_id')]);

        return redirect()->route('activities.show', $activityId)->with('success', 'Règle de conservation remplacée.');
    }



    public function destroy($activityId, $retentionActivityId)
    {
        $activity = Activity::findOrFail($activityId);
        $activity->retentions()->detach($retentionActivityId);

        return redirect()->route('activities.show', $activityId)->with('success', 'Règle de conservation retirée.');
    }

}
