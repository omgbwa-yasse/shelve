<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskType;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Attachment;
use App\Models\Mail;
use App\Models\Container;
use App\Models\Record;
use App\Models\TaskRemember;
use App\Models\TaskSupervision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function myTasks()
    {
        $tasks = Task::whereHas('users', function ($query) {
            $query->where('user_id', auth()->id());
        })->with(['taskType', 'taskStatus', 'users', 'organisations'])->paginate(10);
        dd($tasks);
        return view('tasks.my_tasks', compact('tasks'));
    }

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
        $mails = Mail::all();
        $containers = Container::all();
        $records = Record::all();

        return view('tasks.create', compact('taskTypes', 'taskStatuses', 'users', 'organisations', 'mails', 'containers', 'records'));
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
            'mail_ids' => 'nullable|array',
            'container_ids' => 'nullable|array',
            'record_ids' => 'nullable|array',
            'remember_date_fix' => 'nullable|date',
            'remember_periode' => 'nullable|in:before,after',
            'remember_date_trigger' => 'nullable|in:start,end',
            'remember_limit_number' => 'nullable|integer',
            'remember_limit_date' => 'nullable|date',
            'remember_frequence_value' => 'required|integer',
            'remember_frequence_unit' => 'required|in:year,month,day,hour',
            'remember_user_id' => 'required|exists:users,id',
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

            // Handle task mails
            if (isset($validatedData['mail_ids'])) {
                $task->taskMails()->sync($validatedData['mail_ids']);
            }

            // Handle task containers
            if (isset($validatedData['container_ids'])) {
                $task->taskContainers()->sync($validatedData['container_ids']);
            }

            // Handle task records
            if (isset($validatedData['record_ids'])) {
                $task->taskRecords()->sync($validatedData['record_ids']);
            }

            // Handle task remembers
            TaskRemember::create([
                'task_id' => $task->id,
                'date_fix' => $validatedData['remember_date_fix'],
                'periode' => $validatedData['remember_periode'],
                'date_trigger' => $validatedData['remember_date_trigger'],
                'limit_number' => $validatedData['remember_limit_number'],
                'limit_date' => $validatedData['remember_limit_date'],
                'frequence_value' => $validatedData['remember_frequence_value'],
                'frequence_unit' => $validatedData['remember_frequence_unit'],
                'user_id' => $validatedData['remember_user_id'],
            ]);
        });

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }
    public function show(Task $task)
    {
        $task->load(['taskType', 'taskStatus', 'users', 'organisations', 'attachments', 'taskMails', 'taskContainers', 'taskRecords', 'taskRemembers', 'taskSupervisions']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $taskTypes = TaskType::all();
        $taskStatuses = TaskStatus::all();
        $users = User::all();
        $organisations = Organisation::all();
        $mails = Mail::all();
        $containers = Container::all();
        $records = Record::all();

        $task->load(['taskType', 'users', 'organisations', 'taskMails', 'taskContainers', 'taskRecords', 'taskRemembers', 'taskSupervisions']);

        return view('tasks.edit', compact('task', 'taskTypes', 'taskStatuses', 'users', 'organisations', 'mails', 'containers', 'records'));
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
            'mail_ids' => 'nullable|array',
            'container_ids' => 'nullable|array',
            'record_ids' => 'nullable|array',
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

            // Handle task mails
            if (isset($validatedData['mail_ids'])) {
                $task->taskMails()->sync($validatedData['mail_ids']);
            }

            // Handle task containers
            if (isset($validatedData['container_ids'])) {
                $task->taskContainers()->sync($validatedData['container_ids']);
            }

            // Handle task records
            if (isset($validatedData['record_ids'])) {
                $task->taskRecords()->sync($validatedData['record_ids']);
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
