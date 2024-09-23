<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;


class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::with('parent','communicability')->orderBy('code', 'asc')->get();
        return view('activities.index', compact('activities'));
    }


    public function create()
    {
        $parents = Activity::all();
        return view('activities.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:activities|max:10',
            'name' => 'required|max:100',
            'observation' => 'nullable',
            'parent_id' => 'nullable|exists:activities,id',
        ]);

        Activity::create($request->all());

        return redirect()->route('activities.index')
            ->with('success', 'Activity created successfully.');
    }

    public function show(Activity $activity)
    {
        $activity->load('communicability');
        return view('activities.show', compact('activity'));
    }

    public function edit(Activity $activity)
    {
        $parents = Activity::all();
        return view('activities.edit', compact('activity', 'parents'));
    }

    public function update(Request $request, Activity $activity)
    {
        $request->validate([
            'code' => 'required|unique:activities,code,' . $activity->id . '|max:10',
            'name' => 'required|max:100',
            'observation' => 'nullable',
            'parent_id' => 'nullable|exists:activities,id',
        ]);

        $activity->update($request->all());

        return redirect()->route('activities.index')
            ->with('success', 'Activity updated successfully.');
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('activities.index')
            ->with('success', 'Activity deleted successfully.');
    }
}


