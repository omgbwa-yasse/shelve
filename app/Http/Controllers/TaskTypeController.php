<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskType;
use App\Models\Activity;


class TaskTypeController extends Controller
{
    public function index()
    {
        $taskTypes = TaskType::all();
        return view('settings.tasktype.index', compact('taskTypes'));
    }

    public function create()
    {
        $activities = Activity::all();
        return view('settings.tasktype.create', compact('activities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'activity_id' => 'required|exists:activities,id',
        ]);

        TaskType::create($request->all());

        return redirect()->route('tasktype.index')->with('success', 'Task Type created successfully.');
    }

    public function edit(TaskType $taskType)
    {
        $activities = Activity::all();
        return view('settings.tasktype.edit', compact('taskType', 'activities'));
    }

    public function update(Request $request, TaskType $taskType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'activity_id' => 'required|exists:activities,id',
        ]);

        $taskType->update($request->all());

        return redirect()->route('tasktype.index')->with('success', 'Task Type updated successfully.');
    }

    public function destroy(TaskType $taskType)
    {
        $taskType->delete();

        return redirect()->route('tasktype.index')->with('success', 'Task Type deleted successfully.');
    }
}
