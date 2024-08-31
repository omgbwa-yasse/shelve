<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskType;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['taskTypes', 'taskStatus', 'users', 'organisations']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            $query->where('task_status_id', $request->status);
        }

        $tasks = $query->paginate(10);

        $statuses = TaskStatus::all();

        return view('tasks.index', compact('tasks', 'statuses'));
    }

    public function create()
    {
        $taskTypes = TaskType::all();
        $taskStatuses = TaskStatus::all();
        $users = User::all();
        $organisations = Organisation::all();

        return view('tasks.create', compact('taskTypes', 'taskStatuses', 'users', 'organisations'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:70|unique:tasks',
            'description' => 'required|string',
            'duration' => 'required|integer',
            'task_type_id' => 'required|exists:task_types,id',
            'task_status_id' => 'required|exists:task_statues,id',
            'user_ids' => 'required|array',
            'organisation_ids' => 'required|array',
        ]);

        DB::transaction(function () use ($validatedData, $request) {
            $task = Task::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'duration' => $validatedData['duration'],
                'task_type_id' => $validatedData['task_type_id'],
                'task_status_id' => $validatedData['task_status_id'],
            ]);

            $task->users()->sync($validatedData['user_ids']);
            $task->organisations()->sync($validatedData['organisation_ids']);

            // Handle file uploads if any
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('task_attachments');
                    $task->attachments()->create(['file_path' => $path]);
                }
            }
        });

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['taskTypes', 'taskStatus', 'users', 'organisations', 'attachments']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $taskTypes = TaskType::all();
        $taskStatuses = TaskStatus::all();
        $users = User::all();
        $organisations = Organisation::all();

        $task->load(['taskTypes', 'users', 'organisations']);

        return view('tasks.edit', compact('task', 'taskTypes', 'taskStatuses', 'users', 'organisations'));
    }

    public function update(Request $request, Task $task)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:70|unique:tasks,name,' . $task->id,
            'description' => 'required|string',
            'duration' => 'required|integer',
            'task_type_ids' => 'required|array',
            'task_status_id' => 'required|exists:task_statues,id',
            'user_ids' => 'required|array',
            'organisation_ids' => 'required|array',
        ]);

        DB::transaction(function () use ($task, $validatedData, $request) {
            $task->update([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'duration' => $validatedData['duration'],
                'task_status_id' => $validatedData['task_status_id'],
            ]);

            $task->taskTypes()->sync($validatedData['task_type_ids']);
            $task->users()->sync($validatedData['user_ids']);
            $task->organisations()->sync($validatedData['organisation_ids']);

            // Handle file uploads if any
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('task_attachments');
                    $task->attachments()->create(['file_path' => $path]);
                }
            }
        });

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function removeAttachment(Task $task, $attachmentId)
    {
        $attachment = $task->attachments()->findOrFail($attachmentId);
        Storage::delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Attachment removed successfully.');
    }
}
