<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TaskDependency\StoreTaskDependencyRequest;
use App\Http\Resources\Api\V1\TaskDependencyResource;
use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D17 — Dépendances entre tâches (Gantt : prédécesseur → successeur).
 * L'autorisation passe par `TaskPolicy::update` sur les DEUX tâches
 * impliquées (la dépendance modifie l'ordonnancement des deux).
 */
class TaskDependencyController extends Controller
{
    public function index(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        return response()->json(['data' => TaskDependencyResource::collection(
            $task->successorDependencies()->with('successor')->get()
                ->merge($task->predecessorDependencies()->with('predecessor')->get())
        )]);
    }

    public function store(StoreTaskDependencyRequest $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);
        abort_unless(Auth::user()->hasPermission('task_dependency_create'), 403);

        $successor = Task::findOrFail($request->validated('successor_id'));
        $this->authorize('update', $successor);

        abort_if($successor->id === $task->id, 422, 'Une tâche ne peut pas dépendre d\'elle-même.');

        $dependency = TaskDependency::create([
            'predecessor_id' => $task->id,
            'successor_id' => $successor->id,
            'type' => $request->validated('type', TaskDependency::TYPE_FINISH_TO_START),
            'lag_days' => $request->validated('lag_days', 0),
        ]);

        return response()->json(
            ['data' => new TaskDependencyResource($dependency->load(['predecessor', 'successor']))],
            201,
            ['Location' => "/api/v1/task-dependencies/{$dependency->id}"]
        );
    }

    public function destroy(TaskDependency $taskDependency): Response
    {
        $taskDependency->load('predecessor');
        $this->authorize('update', $taskDependency->predecessor);
        abort_unless(Auth::user()->hasPermission('task_dependency_delete'), 403);

        $taskDependency->delete();

        return response()->noContent();
    }
}
