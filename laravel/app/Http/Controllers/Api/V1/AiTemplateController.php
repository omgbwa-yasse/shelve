<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AiTemplate\StoreAiTemplateRequest;
use App\Http\Requests\Api\V1\AiTemplate\UpdateAiTemplateRequest;
use App\Http\Resources\Api\V1\AiTemplateResource;
use App\Models\AiTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * D14 — modèles de documents IA (référentiel global). Porté le 2026-08-04.
 *
 * `store` reçoit un fichier (`template_file`) comme le Blade : nom de fichier, chemin,
 * mime et taille sont posés côté serveur, `created_by` depuis l'agent authentifié.
 *
 * ⚠️ TODO (E2, phase 3) : `download` (téléversement du binaire) et `preview` (lecture
 * du contenu via `AiTemplateReader`) ne sont pas portés — exports/lecture de fichiers,
 * classe E2.
 */
class AiTemplateController extends Controller
{
    use HandlesApiQueries;

    private const ALLOWED_EXTENSIONS = ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'txt', 'md', 'csv', 'odt', 'ods', 'html'];

    private const FILTERABLE = ['id', 'name', 'category', 'mime_type', 'size', 'created_by', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'category', 'mime_type', 'size', 'created_by', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['creator'];

    /**
     * GET /api/v1/ai-templates
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AiTemplate::class);

        $query = AiTemplate::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, AiTemplateResource::class));
    }

    /**
     * GET /api/v1/ai-templates/{id}
     */
    public function show(AiTemplate $aiTemplate): JsonResponse
    {
        $this->authorize('view', $aiTemplate);

        return response()->json(['data' => new AiTemplateResource($aiTemplate)]);
    }

    /**
     * POST /api/v1/ai-templates
     */
    public function store(StoreAiTemplateRequest $request): JsonResponse
    {
        $this->authorize('create', AiTemplate::class);

        $file = $request->file('template_file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new UnprocessableEntityHttpException("Format non autorisé : .{$ext}");
        }

        $fileName = $file->getClientOriginalName();
        $dir = 'ai/templates/' . date('Y/m');
        $path = Storage::disk('local')->putFileAs($dir, $file, Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . $ext);

        $aiTemplate = AiTemplate::create([
            'name' => $request->input('name', pathinfo($fileName, PATHINFO_FILENAME)),
            'category' => $request->input('category'),
            'file_name' => $fileName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'description' => $request->input('description'),
            'created_by' => Auth::id(),
        ]);

        return response()->json(
            ['data' => new AiTemplateResource($aiTemplate)],
            201,
            ['Location' => "/api/v1/ai-templates/{$aiTemplate->id}"]
        );
    }

    /**
     * PATCH /api/v1/ai-templates/{id}
     */
    public function update(UpdateAiTemplateRequest $request, AiTemplate $aiTemplate): JsonResponse
    {
        $this->authorize('update', $aiTemplate);

        $aiTemplate->update($request->validated());

        return response()->json(['data' => new AiTemplateResource($aiTemplate->fresh())]);
    }

    /**
     * DELETE /api/v1/ai-templates/{id}
     */
    public function destroy(AiTemplate $aiTemplate): Response
    {
        $this->authorize('delete', $aiTemplate);

        Storage::disk('local')->delete($aiTemplate->file_path);
        $aiTemplate->delete();

        return response()->noContent();
    }
}
