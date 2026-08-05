<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Task\StoreTaskRequest;
use App\Http\Requests\Api\V1\Task\UpdateTaskRequest;
use App\Http\Resources\Api\V1\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D12 — tâches.
 *
 * Relevé contre `TaskController` (relu le 2026-08-04). Vérification faite : le
 * modèle n'utilise PAS `BelongsToOrganisation` et le contrôleur Blade ne filtre
 * pas par organisation — tâches traitées en référentiel global (motif D01).
 * `created_by` / `updated_by` posés depuis l'agent.
 */
class TaskController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'title', 'status', 'priority', 'assigned_to', 'workflow_instance_id', 'parent_task_id', 'taskable_type', 'taskable_id', 'due_date', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'title', 'status', 'priority', 'assigned_to', 'workflow_instance_id', 'parent_task_id', 'due_date', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['assignedUser', 'creator', 'updater', 'completer', 'workflowInstance', 'parentTask', 'subTasks', 'taskable', 'comments', 'attachments', 'reminders', 'watchers'];

    /**
     * GET /api/v1/tasks
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $query = Task::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'created_at');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, TaskResource::class));
    }

    /**
     * GET /api/v1/tasks/{id}
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load(['assignedUser', 'creator', 'comments.user', 'attachments', 'watchers.user']);

        return response()->json(['data' => new TaskResource($task)]);
    }

    /**
     * POST /api/v1/tasks
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $task = Task::create($request->validated() + [
            'created_by' => Auth::id(),
            'organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return response()->json(
            ['data' => new TaskResource($task->fresh())],
            201,
            ['Location' => "/api/v1/tasks/{$task->id}"]
        );
    }

    /**
     * PATCH /api/v1/tasks/{id}
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->update($request->validated() + ['updated_by' => Auth::id()]);

        return response()->json(['data' => new TaskResource($task->fresh())]);
    }

    /**
     * DELETE /api/v1/tasks/{id}
     */
    public function destroy(Task $task): Response
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }
}
