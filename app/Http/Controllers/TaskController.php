<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Role;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['assignedUser', 'creator', 'updater', 'workflowInstance'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'me') {
                $query->where('assigned_to', Auth::id());
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'users'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        $roles = Role::with('users')->orderBy('name')->get();
        return view('tasks.create', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:190',
            'description' => 'nullable|string',
            'status'      => 'nullable|in:pending,in_progress,completed,cancelled',
            'priority'    => 'required|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        // Default status to pending when creating
        $validated['status'] = $validated['status'] ?? 'pending';

        $task = Task::create([
            ...$validated,
            'organisation_id' => Auth::user()->current_organisation_id,
            'created_by'      => Auth::id(),
        ]);

        return redirect()->route('tasks.show', $task)
            ->with('success', __('Tâche créée avec succès.'));
    }

    public function show(Task $task)
    {
        $task->load(['assignedUser', 'creator', 'comments.user', 'attachments', 'watchers.user', 'history']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $users = User::orderBy('name')->get();
        $roles = Role::with('users')->orderBy('name')->get();
        return view('tasks.edit', compact('task', 'users', 'roles'));
    }

    public function update(Request $request, Task $task)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:190',
            'description' => 'nullable|string',
            'status'      => 'required|in:pending,in_progress,completed,cancelled',
            'priority'    => 'required|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date'    => 'nullable|date',
        ]);

        $task->update([
            ...$validated,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('tasks.show', $task)
            ->with('success', __('Tâche mise à jour avec succès.'));
    }

    public function complete(Request $request, Task $task)
    {
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        $task->update([
            'status'       => 'completed',
            'completed_at' => now(),
            'completed_by' => Auth::id(),
            'updated_by'   => Auth::id(),
        ]);

        // Save completion note as a TaskComment if provided
        $note = $request->input('completion_note');
        if ($note && trim($note) !== '') {
            TaskComment::create([
                'task_id' => $task->id,
                'comment' => '✅ ' . __('Note de clôture') . ' : ' . trim($note),
                'user_id' => Auth::id(),
            ]);
        }

        return redirect()->back()
            ->with('success', __('Tâche marquée comme terminée.'));
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', __('Tâche supprimée.'));
    }
}
