<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\WorkflowInstance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Tableau de bord des workflows (étape 10) : échéances, retards par workflow,
 * étape et utilisateur, taux de respect des échéances calculé sur données réelles.
 */
class WorkflowDashboardController extends Controller
{
    public function index()
    {
        $query = Task::workflow();

        if (! Auth::user()->isSuperAdmin()) {
            $query->where('organisation_id', Auth::user()->current_organisation_id);
        }

        $totals = $query->clone()
            ->selectRaw("
                COUNT(*) as total_tasks,
                SUM(status = 'pending') as pending,
                SUM(status = 'in_progress') as in_progress,
                SUM(status = 'completed') as completed,
                SUM(status IN ('pending','in_progress') AND due_date IS NOT NULL AND due_date < NOW()) as overdue
            ")
            ->first();

        $onTimeCompleted = $query->clone()
            ->where('status', 'completed')
            ->whereNotNull('due_date')
            ->whereNotNull('completed_at')
            ->whereColumn('completed_at', '<=', 'due_date')
            ->count();

        $completedWithDueDate = $query->clone()
            ->where('status', 'completed')
            ->whereNotNull('due_date')
            ->count();

        $onTimeRate = $completedWithDueDate > 0
            ? round($onTimeCompleted / $completedWithDueDate * 100, 1)
            : null;

        $overdueTasks = $query->clone()
            ->overdue()
            ->with(['assignedUser', 'workflowInstance.definition'])
            ->orderBy('due_date')
            ->limit(50)
            ->get();

        $byWorkflow = WorkflowInstance::query()
            ->whereHas('tasks', fn ($q) => $q->whereNotNull('due_date'))
            ->withCount(['tasks' => fn ($q) => $q->overdue()])
            ->with(['definition', 'tasks' => fn ($q) => $q->overdue()->count()])
            ->get()
            ->filter(fn ($instance) => $instance->tasks_count > 0)
            ->map(fn ($instance) => [
                'name' => $instance->name,
                'overdue' => $instance->tasks_count,
            ]);

        $byStep = $query->clone()
            ->select('task_key', DB::raw('SUM(status IN ("pending","in_progress") AND due_date IS NOT NULL AND due_date < NOW()) as overdue'))
            ->groupBy('task_key')
            ->orderByDesc('overdue')
            ->get();

        $byUser = $query->clone()
            ->whereNotNull('assigned_to')
            ->select('assigned_to', DB::raw('SUM(status IN ("pending","in_progress") AND due_date IS NOT NULL AND due_date < NOW()) as overdue'))
            ->groupBy('assigned_to')
            ->orderByDesc('overdue')
            ->with('assignedUser')
            ->get()
            ->map(fn ($row) => [
                'user' => User::find($row->assigned_to)?->name ?? '—',
                'overdue' => (int) $row->overdue,
            ]);

        return view('workflows.dashboard', compact(
            'totals',
            'onTimeRate',
            'overdueTasks',
            'byWorkflow',
            'byStep',
            'byUser'
        ));
    }
}
