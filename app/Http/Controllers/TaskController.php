<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskType;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Attachment;
use App\Models\TaskRemember;
use App\Models\TaskRecord;
use App\Models\TaskSupervision;
use App\Models\TaskMail;
use App\Models\TaskContainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['taskType', 'taskStatus', 'users', 'organisations']);

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
        $taskRemembers = TaskRemember::all();
        $taskRecords = TaskRecord::all();
        $taskSupervisions = TaskSupervision::all();
        $taskMails = TaskMail::all();
        $taskContainers = TaskContainer::all();

        return view('tasks.create', compact('taskTypes', 'taskStatuses', 'users', 'organisations', 'taskRemembers', 'taskRecords', 'taskSupervisions', 'taskMails', 'taskContainers'));
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
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'task_remember_ids' => 'nullable|array',
            'task_record_ids' => 'nullable|array',
            'task_supervision_ids' => 'nullable|array',
            'task_mail_ids' => 'nullable|array',
            'task_container_ids' => 'nullable|array',
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
                    $attachment = Attachment::create([
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'crypt' => $file->hashName(),
                        'size' => $file->getSize(),
                        'creator_id' => auth()->id(),
                    ]);
                    $task->attachments()->attach($attachment->id);
                }
            }

            // Handle task remembers
            if (isset($validatedData['task_remember_ids'])) {
                $task->taskRemembers()->sync($validatedData['task_remember_ids']);
            }

            // Handle task records
            if (isset($validatedData['task_record_ids'])) {
                $task->taskRecords()->sync($validatedData['task_record_ids']);
            }

            // Handle task supervisions
            if (isset($validatedData['task_supervision_ids'])) {
                $task->taskSupervisions()->sync($validatedData['task_supervision_ids']);
            }

            // Handle task mails
            if (isset($validatedData['task_mail_ids'])) {
                $task->taskMails()->sync($validatedData['task_mail_ids']);
            }

            // Handle task containers
            if (isset($validatedData['task_container_ids'])) {
                $task->taskContainers()->sync($validatedData['task_container_ids']);
            }
        });

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['taskType', 'taskStatus', 'users', 'organisations', 'attachments', 'taskRemembers', 'taskRecords', 'taskSupervisions', 'taskMails', 'taskContainers']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $taskTypes = TaskType::all();
        $taskStatuses = TaskStatus::all();
        $users = User::all();
        $organisations = Organisation::all();
        $taskRemembers = TaskRemember::all();
        $taskRecords = TaskRecord::all();
        $taskSupervisions = TaskSupervision::all();
        $taskMails = TaskMail::all();
        $taskContainers = TaskContainer::all();

        $task->load(['taskType', 'users', 'organisations', 'taskRemembers', 'taskRecords', 'taskSupervisions', 'taskMails', 'taskContainers']);

        return view('tasks.edit', compact('task', 'taskTypes', 'taskStatuses', 'users', 'organisations', 'taskRemembers', 'taskRecords', 'taskSupervisions', 'taskMails', 'taskContainers'));
    }

    public function update(Request $request, Task $task)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:70|unique:tasks,name,' . $task->id,
            'description' => 'required|string',
            'duration' => 'required|integer',
            'task_type_id' => 'required|exists:task_types,id',
            'task_status_id' => 'required|exists:task_statues,id',
            'user_ids' => 'required|array',
            'organisation_ids' => 'required|array',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
            'task_remember_ids' => 'nullable|array',
            'task_record_ids' => 'nullable|array',
            'task_supervision_ids' => 'nullable|array',
            'task_mail_ids' => 'nullable|array',
            'task_container_ids' => 'nullable|array',
        ]);

        DB::transaction(function () use ($task, $validatedData, $request) {
            $task->update([
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
                    $attachment = Attachment::create([
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                        'crypt' => $file->hashName(),
                        'size' => $file->getSize(),
                        'creator_id' => auth()->id(),
                    ]);
                    $task->attachments()->attach($attachment->id);
                }
            }

            // Handle task remembers
            if (isset($validatedData['task_remember_ids'])) {
                $task->taskRemembers()->sync($validatedData['task_remember_ids']);
            }

            // Handle task records
            if (isset($validatedData['task_record_ids'])) {
                $task->taskRecords()->sync($validatedData['task_record_ids']);
            }

            // Handle task supervisions
            if (isset($validatedData['task_supervision_ids'])) {
                $task->taskSupervisions()->sync($validatedData['task_supervision_ids']);
            }

            // Handle task mails
            if (isset($validatedData['task_mail_ids'])) {
                $task->taskMails()->sync($validatedData['task_mail_ids']);
            }

            // Handle task containers
            if (isset($validatedData['task_container_ids'])) {
                $task->taskContainers()->sync($validatedData['task_container_ids']);
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
        Storage::delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment removed successfully.');
    }

    public function downloadAttachment(Task $task, $attachmentId)
    {
        $attachment = $task->attachments()->findOrFail($attachmentId);
        return Storage::download($attachment->path);
    }
}
