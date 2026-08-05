<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Setting\StoreSettingRequest;
use App\Http\Requests\Api\V1\Setting\UpdateSettingRequest;
use App\Http\Resources\Api\V1\SettingResource;
use App\Models\Setting;
use App\Services\Settings\SettingValueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D01 — relu et validé le 2026-08-04 contre `SettingController` (Blade) et le schéma.
 *
 * `settings` est la seule table du domaine à porter `organisation_id` : la portée
 * `forUserAndOrganisation` du modèle s'applique donc à l'index (risque R03).
 *
 * La logique de conversion/validation des valeurs est partagée avec le contrôleur
 * Blade via `SettingValueService` (ruban e du plan — une seule source de vérité).
 */
class SettingController extends Controller
{
    use HandlesApiQueries;

    public function __construct(private SettingValueService $settingValue)
    {
    }

    private const FILTERABLE = ['id', 'category_id', 'name', 'type', 'is_system', 'user_id', 'organisation_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'category_id', 'name', 'type', 'is_system', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['category', 'user', 'organisation'];

    /**
     * GET /api/v1/settings
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        $query = Setting::query();

        // Même portée que le back-office Blade : l'agent ne voit que ses paramètres,
        // ceux de son organisation et les paramètres globaux.
        $query->forUserAndOrganisation(Auth::id(), Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, SettingResource::class));
    }

    /**
     * GET /api/v1/settings/{id}
     */
    public function show(Setting $setting): JsonResponse
    {
        $this->authorize('view', $setting);

        return response()->json(['data' => new SettingResource($setting)]);
    }

    /**
     * POST /api/v1/settings
     */
    public function store(StoreSettingRequest $request): JsonResponse
    {
        $this->authorize('create', Setting::class);

        $setting = Setting::create($request->validated());

        return response()->json(
            ['data' => new SettingResource($setting)],
            201,
            ['Location' => "/api/v1/settings/{$setting->id}"]
        );
    }

    /**
     * PATCH /api/v1/settings/{id}
     */
    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        $this->authorize('update', $setting);

        $setting->update($request->validated());

        return response()->json(['data' => new SettingResource($setting->fresh())]);
    }

    /**
     * DELETE /api/v1/settings/{id}
     */
    public function destroy(Setting $setting): Response
    {
        $this->authorize('delete', $setting);

        $setting->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/settings/{id}/set-value — valeur personnalisée du paramètre.
     *
     * Reprend l'intention de `SettingController::setValue()` (Blade) — conversion et
     * validation selon le type et les contraintes — mais écrit sur la ligne du
     * paramètre lui-même : le schéma impose `unique:settings,name`, donc une valeur
     * personnalisée ne peut pas être une seconde ligne de même nom (le `create` du
     * contrôleur Blade échouait sur ce doublon). L'API corrige ce défaut.
     */
    public function setValue(Request $request, Setting $setting): JsonResponse
    {
        $this->authorize('update', $setting);

        $request->validate(['value' => 'required']);

        $value = $this->settingValue->convertValueToType($request->input('value'), $setting->type);

        if (!$this->settingValue->validateValueType($value, $setting->type, $setting->constraints)) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'La valeur ne correspond pas au type de paramètre ou aux contraintes', 'errors' => ['value' => ['La valeur ne correspond pas au type de paramètre ou aux contraintes']]],
                422
            );
        }

        $setting->value = $value;
        $setting->save();

        return response()->json(['data' => new SettingResource($setting->fresh())]);
    }

    /**
     * DELETE /api/v1/settings/{id}/reset-value — retour à la valeur par défaut.
     *
     * Reprend `SettingController::resetValue()` (Blade) : la valeur personnalisée
     * est effacée, `effective_value` revient à `default_value`.
     */
    public function resetValue(Setting $setting): JsonResponse
    {
        $this->authorize('update', $setting);

        $setting->value = null;
        $setting->save();

        return response()->json(['data' => new SettingResource($setting->fresh())]);
    }
}
