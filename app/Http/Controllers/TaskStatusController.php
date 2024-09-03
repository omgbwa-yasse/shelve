<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\TaskStatus;


class TaskStatusController extends Controller
{
    public function index()
    {
        $taskStatuses = TaskStatus::all();
        return view('settings.taskstatus.index', compact('taskStatuses'));
    }

    public function create()
    {
        return view('settings.taskstatus.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        TaskStatus::create($request->all());

        return redirect()->route('taskstatus.index')->with('success', 'Task Status created successfully.');
    }

    public function edit(TaskStatus $taskStatus)
    {
        return view('settings.taskstatus.edit', compact('taskStatus'));
    }

    public function update(Request $request, TaskStatus $taskStatus)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $taskStatus->update($request->all());

        return redirect()->route('taskstatus.index')->with('success', 'Task Status updated successfully.');
    }

    public function destroy(TaskStatus $taskStatus)
    {
        if ($taskStatus) {
            $taskStatus->delete();
            return redirect()->route('taskstatus.index')->with('success', 'Task Status deleted successfully.');
        }

        return redirect()->route('taskstatus.index')->with('error', 'Task Status not found.');
    }
}
