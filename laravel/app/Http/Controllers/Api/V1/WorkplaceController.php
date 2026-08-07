<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Workplace\StoreWorkplaceRequest;
use App\Http\Requests\Api\V1\Workplace\UpdateWorkplaceRequest;
use App\Http\Resources\Api\V1\WorkplaceResource;
use App\Models\Workplace;
use App\Models\WorkplaceTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * D12 — espaces de travail (workplaces), **org-scopés** via `organisation_id`
 * (trait BelongsToOrganisation) : l'index ne renvoie que les workplaces de
 * l'organisation courante, et toute ressource d'une autre organisation répond
 * 404 (jamais 403 — voir CONVENTIONS §4).
 *
 * Relevé contre `WorkplaceController` (relu le 2026-08-04). `code`, `status`,
 * `organisation_id`, `owner_id`, `created_by`/`updated_by` sont posés côté
 * serveur ; le créateur devient membre `owner` du workplace.
 */
class WorkplaceController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'name', 'category_id', 'is_public', 'allow_external_sharing', 'status', 'organisation_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'name', 'category_id', 'is_public', 'status', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['category', 'organisation', 'owner', 'members.user', 'folders', 'documents', 'activities'];

    /**
     * GET /api/v1/workplaces
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Workplace::class);

        $query = Workplace::byOrganisation(Auth::user()->current_organisation_id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn ($q) => $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%'));
        }

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, WorkplaceResource::class));
    }

    /**
     * GET /api/v1/workplaces/{id|code}
     *
     * Accepte `?include=category,owner,members.user,activities` pour alimenter
     * le tableau de bord du workplace côté Next.
     */
    public function show(Request $request, Workplace $workplace): JsonResponse
    {
        // Isolation : un workplace d'une autre organisation est 404.
        $query = Workplace::byOrganisation(Auth::user()->current_organisation_id)->whereKey($workplace->id);

        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $workplace = $query->firstOrFail();

        $this->authorize('view', $workplace);

        return response()->json(['data' => new WorkplaceResource($workplace)]);
    }

    /**
     * POST /api/v1/workplaces
     */
    public function store(StoreWorkplaceRequest $request): JsonResponse
    {
        $this->authorize('create', Workplace::class);

        $data = $request->validated();

        DB::beginTransaction();
        try {
            $workplace = Workplace::create([
                ...$data,
                'code' => mb_strtolower(trim($data['code'])),
                'organisation_id' => Auth::user()->current_organisation_id,
                'owner_id' => Auth::id(),
                'created_by' => Auth::id(),
                'status' => 'active',
            ]);

            // Le créateur devient membre owner, comme en Blade.
            $workplace->members()->create([
                'user_id' => Auth::id(),
                'role' => 'owner',
                'can_create_folders' => true,
                'can_create_documents' => true,
                'can_delete' => true,
                'can_share' => true,
                'can_invite' => true,
                'joined_at' => now(),
            ]);

            if (!empty($data['template_id'])) {
                $template = WorkplaceTemplate::find($data['template_id']);
                $template?->incrementUsage();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json(
            ['data' => new WorkplaceResource($workplace->fresh())],
            201,
            ['Location' => "/api/v1/workplaces/{$workplace->code}"]
        );
    }

    /**
     * PATCH /api/v1/workplaces/{id}
     */
    public function update(UpdateWorkplaceRequest $request, Workplace $workplace): JsonResponse
    {
        // Isolation : un workplace d'une autre organisation est 404.
        $workplace = Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);

        $this->authorize('update', $workplace);

        $workplace->update([
            ...$request->validated(),
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['data' => new WorkplaceResource($workplace->fresh())]);
    }

    /**
     * DELETE /api/v1/workplaces/{id}
     */
    public function destroy(Workplace $workplace): Response
    {
        // Isolation : un workplace d'une autre organisation est 404.
        $workplace = Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);

        $this->authorize('delete', $workplace);

        $workplace->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/workplaces/{id}/archive
     */
    public function archive(Workplace $workplace): JsonResponse
    {
        $workplace = Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);

        $this->authorize('update', $workplace);

        $workplace->update(['status' => 'archived']);

        return response()->json(['data' => new WorkplaceResource($workplace->fresh())]);
    }

    /**
     * GET /api/v1/workplaces/{id}/settings
     */
    public function settings(Workplace $workplace): JsonResponse
    {
        $workplace = Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);

        $this->authorize('update', $workplace);

        return response()->json(['data' => new WorkplaceResource($workplace)]);
    }

    /**
     * GET /api/v1/workplaces/{id|code}/calendar
     *
     * Agrège les éléments datés du workplace pour la vue Calendrier : dates de
     * début/fin des projets rattachés, jalons (due_date) et tâches (due_date).
     */
    public function calendar(Request $request, Workplace $workplace): JsonResponse
    {
        $workplace = Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);

        $this->authorize('view', $workplace);

        $events = [];

        $projects = $workplace->projects()->with(['milestones', 'tasks'])->get();

        foreach ($projects as $project) {
            if ($project->start_date) {
                $events[] = [
                    'date' => $project->start_date->toDateString(),
                    'type' => 'project_start',
                    'title' => $project->name,
                    'subtitle' => 'Début du projet',
                    'project_id' => $project->id,
                    'color' => '#2563eb',
                ];
            }

            if ($project->end_date) {
                $events[] = [
                    'date' => $project->end_date->toDateString(),
                    'type' => 'project_end',
                    'title' => $project->name,
                    'subtitle' => 'Fin du projet',
                    'project_id' => $project->id,
                    'color' => '#7c3aed',
                ];
            }

            foreach ($project->milestones as $milestone) {
                if ($milestone->due_date) {
                    $events[] = [
                        'date' => $milestone->due_date->toDateString(),
                        'type' => 'milestone',
                        'title' => $milestone->name,
                        'subtitle' => 'Jalon — ' . $project->name,
                        'project_id' => $project->id,
                        'color' => '#059669',
                    ];
                }
            }

            foreach ($project->tasks as $task) {
                if ($task->due_date) {
                    $events[] = [
                        'date' => $task->due_date->toDateString(),
                        'type' => 'task_due',
                        'title' => $task->title,
                        'subtitle' => 'Tâche — ' . $project->name,
                        'project_id' => $project->id,
                        'color' => '#d97706',
                    ];
                }
            }
        }

        usort($events, fn ($a, $b) => strcmp($a['date'], $b['date']));

        return response()->json(['data' => $events]);
    }

    private function generateWorkplaceCode(): string
    {
        $year = date('Y');
        $lastWorkplace = Workplace::whereYear('created_at', $year)
            ->orderBy('code', 'desc')
            ->first();

        if ($lastWorkplace && preg_match('/WP-' . $year . '-(\d+)/', $lastWorkplace->code, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        do {
            $code = sprintf('WP-%s-%04d', $year, $number);
            $exists = Workplace::where('code', $code)->exists();
            if ($exists) {
                $number++;
            }
        } while ($exists);

        return $code;
    }
}
