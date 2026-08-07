<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Container\StoreContainerRequest;
use App\Http\Requests\Api\V1\Container\UpdateContainerRequest;
use App\Http\Resources\Api\V1\ContainerResource;
use App\Models\Container;
use App\Models\Shelf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D03 — relu et validé le 2026-08-04 contre `ContainerController` et le schéma.
 *
 * Les conteneurs sont org-scopés (R03) : par héritage de leur rayonnage/salle, et via
 * `creator_organisation_id`. L'index est borné à l'organisation courante (filtre
 * `shelf_id` conservé), et une ressource hors périmètre répond 404.
 * `creator_id` / `creator_organisation_id` posés depuis l'agent.
 */
class ContainerController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'code', 'shelve_id', 'status_id', 'property_id', 'is_archived', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'code', 'shelve_id', 'status_id', 'property_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['shelf', 'status', 'property', 'records'];

    /**
     * GET /api/v1/containers
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Container::class);

        $query = Container::inOrganisation(Auth::user()->current_organisation_id);

        // Filtre spécifique conservé du contrôleur Blade (le champ de la table est
        // `shelve_id`, le paramètre historique `shelf_id`).
        if ($request->filled('shelf_id')) {
            $query->where('shelve_id', $request->input('shelf_id'));
        }

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, ContainerResource::class));
    }

    /**
     * GET /api/v1/containers/{id}
     */
    public function show(Container $container): JsonResponse
    {
        $this->authorize('view', $container);

        $container = Container::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($container->id);

        return response()->json(['data' => new ContainerResource($container)]);
    }

    /**
     * POST /api/v1/containers
     */
    public function store(StoreContainerRequest $request): JsonResponse
    {
        $this->authorize('create', Container::class);

        // Un conteneur doit être posé sur un rayonnage de l'organisation courante.
        if (!Shelf::inOrganisation(Auth::user()->current_organisation_id)->whereKey($request->input('shelve_id'))->exists()) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Le rayonnage n\'appartient pas à votre organisation.', 'errors' => ['shelve_id' => ['Le rayonnage n\'appartient pas à votre organisation.']]],
                422
            );
        }

        $container = Container::create($request->validated() + [
            'creator_id' => Auth::id(),
            'creator_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return response()->json(
            ['data' => new ContainerResource($container->load('shelf', 'status', 'property'))],
            201,
            ['Location' => "/api/v1/containers/{$container->id}"]
        );
    }

    /**
     * PATCH /api/v1/containers/{id}
     */
    public function update(UpdateContainerRequest $request, Container $container): JsonResponse
    {
        $this->authorize('update', $container);

        $container = Container::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($container->id);

        $container->update($request->validated());

        return response()->json(['data' => new ContainerResource($container->fresh()->load('shelf', 'status', 'property'))]);
    }

    /**
     * DELETE /api/v1/containers/{id}
     */
    public function destroy(Container $container): Response
    {
        $this->authorize('delete', $container);

        $container = Container::inOrganisation(Auth::user()->current_organisation_id)->findOrFail($container->id);

        $container->delete();

        return response()->noContent();
    }
}
