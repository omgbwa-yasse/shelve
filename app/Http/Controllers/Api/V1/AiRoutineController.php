<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AiRoutine\StoreAiRoutineRequest;
use App\Http\Requests\Api\V1\AiRoutine\UpdateAiRoutineRequest;
use App\Http\Resources\Api\V1\AiRoutineResource;
use App\Models\AiRoutine;
use App\Services\AI\AiRoutineExecutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Routines programmées de l'assistant IA (onglet "Routine" du panneau
 * latéral) — connectées aux prompts/skills existants (D14). Voir
 * `AiRoutine::computeNextRunAt()` et la commande `ai:routines:run-due`.
 */
class AiRoutineController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'schedule_type', 'is_enabled', 'created_at'];
    private const SORTABLE = ['id', 'name', 'schedule_type', 'next_run_at', 'last_run_at', 'created_at'];
    private const INCLUDABLE = ['prompt', 'skill', 'creator'];

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AiRoutine::class);

        $query = AiRoutine::byOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->orderByDesc('created_at')->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, AiRoutineResource::class));
    }

    public function show(AiRoutine $aiRoutine): JsonResponse
    {
        $this->authorize('view', $aiRoutine);

        $aiRoutine = AiRoutine::byOrganisation(Auth::user()->current_organisation_id)
            ->with(['prompt', 'skill'])
            ->findOrFail($aiRoutine->id);

        return response()->json(['data' => new AiRoutineResource($aiRoutine)]);
    }

    public function store(StoreAiRoutineRequest $request): JsonResponse
    {
        $this->authorize('create', AiRoutine::class);

        $data = $request->validated();

        $routine = new AiRoutine($data + [
            'organisation_id' => Auth::user()->current_organisation_id,
            'created_by' => Auth::id(),
        ]);
        $routine->next_run_at = $routine->schedule_type === AiRoutine::SCHEDULE_ONCE ? now() : $routine->computeNextRunAt();
        $routine->save();

        return response()->json(
            ['data' => new AiRoutineResource($routine->load(['prompt', 'skill']))],
            201,
            ['Location' => "/api/v1/ai/routines/{$routine->id}"]
        );
    }

    public function update(UpdateAiRoutineRequest $request, AiRoutine $aiRoutine): JsonResponse
    {
        $this->authorize('update', $aiRoutine);

        $aiRoutine = AiRoutine::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($aiRoutine->id);

        $data = $request->validated();
        $aiRoutine->fill($data);

        if ($aiRoutine->isDirty(['schedule_type', 'run_time', 'day_of_week'])) {
            $aiRoutine->next_run_at = $aiRoutine->schedule_type === AiRoutine::SCHEDULE_ONCE ? now() : $aiRoutine->computeNextRunAt();
        }

        $aiRoutine->save();

        return response()->json(['data' => new AiRoutineResource($aiRoutine->fresh(['prompt', 'skill']))]);
    }

    public function destroy(AiRoutine $aiRoutine): Response
    {
        $this->authorize('delete', $aiRoutine);

        $aiRoutine = AiRoutine::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($aiRoutine->id);
        $aiRoutine->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/ai/routines/{id}/run — exécution immédiate, hors planification.
     */
    public function run(AiRoutine $aiRoutine, AiRoutineExecutionService $executor): JsonResponse
    {
        $this->authorize('update', $aiRoutine);

        $aiRoutine = AiRoutine::byOrganisation(Auth::user()->current_organisation_id)
            ->with(['prompt', 'skill'])
            ->findOrFail($aiRoutine->id);

        $result = $executor->execute($aiRoutine);
        $aiRoutine->markRun($result['status'], $result['output']);

        return response()->json(['data' => new AiRoutineResource($aiRoutine->fresh(['prompt', 'skill']))]);
    }
}
