<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecordArtifactService;
use App\Models\RecordArtifact;
use App\Models\RecordArtifactExhibition;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Artifacts', description: 'Endpoints for managing artifacts and museum collections')]
class RecordArtifactApiController extends Controller
{
    protected RecordArtifactService $service;

    public function __construct(RecordArtifactService $service)
    {
        $this->service = $service;
    }

    #[OA\Get(
        path: '/api/v1/artifacts',
        summary: 'List all artifacts with filters',
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'category', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RecordArtifact::query()->with(['creator', 'organisation']);

            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('artist', 'like', "%{$search}%");
                });
            }

            $artifacts = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $artifacts->items(),
                'pagination' => [
                    'current_page' => $artifacts->currentPage(),
                    'total' => $artifacts->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/artifacts/{id}',
        summary: 'Get a specific artifact',
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 404, description: 'Artifact not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $artifact = RecordArtifact::with(['creator', 'organisation', 'exhibitions', 'loans'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $artifact]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }

    #[OA\Post(
        path: '/api/v1/artifacts',
        summary: 'Create a new artifact',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'organisation_id', 'category'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255),
                    new OA\Property(property: 'organisation_id', type: 'integer'),
                    new OA\Property(property: 'category', type: 'string'),
                    new OA\Property(property: 'artist', type: 'string'),
                    new OA\Property(property: 'creation_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'acquisition_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'estimated_value', type: 'number', format: 'float'),
                    new OA\Property(property: 'insurance_value', type: 'number', format: 'float'),
                    new OA\Property(property: 'dimensions', type: 'object'),
                    new OA\Property(property: 'materials', type: 'array', items: new OA\Items(type: 'string')),
                    new OA\Property(property: 'metadata', type: 'object'),
                ]
            )
        ),
        tags: ['Artifacts'],
        responses: [
            new OA\Response(response: 201, description: 'Artifact created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'organisation_id' => 'required|exists:organisations,id',
            'category' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $organisation = Organisation::findOrFail($request->organisation_id);
            $artifact = $this->service->createArtifact($request->all(), Auth::user(), $organisation);
            return response()->json(['success' => true, 'data' => $artifact], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Put(
        path: '/api/v1/artifacts/{id}',
        summary: 'Update an existing artifact',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'artist', type: 'string'),
                    new OA\Property(property: 'category', type: 'string'),
                    new OA\Property(property: 'metadata', type: 'object'),
                ]
            )
        ),
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Artifact updated successfully'),
            new OA\Response(response: 404, description: 'Artifact not found'),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $artifact = RecordArtifact::findOrFail($id);
            $updated = $this->service->updateArtifact($artifact, $request->all());
            return response()->json(['success' => true, 'data' => $updated]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: '/api/v1/artifacts/{id}',
        summary: 'Delete an artifact',
        security: [['sanctum' => []]],
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Artifact deleted successfully'),
            new OA\Response(response: 404, description: 'Artifact not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            RecordArtifact::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/artifacts/{id}/exhibitions',
        summary: 'Add artifact to an exhibition',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'exhibition_name', type: 'string'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'location', type: 'string'),
                ]
            )
        ),
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Added to exhibition successfully'),
            new OA\Response(response: 404, description: 'Artifact not found'),
        ]
    )]
    public function addToExhibition(Request $request, int $id): JsonResponse
    {
        try {
            $artifact = RecordArtifact::findOrFail($id);
            $exhibition = $this->service->addToExhibition($artifact, $request->all(), true);
            return response()->json(['success' => true, 'data' => $exhibition], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/artifacts/{id}/loan',
        summary: 'Loan an artifact',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'borrower', type: 'string'),
                    new OA\Property(property: 'loan_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'notes', type: 'string'),
                ]
            )
        ),
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Loan created successfully'),
            new OA\Response(response: 404, description: 'Artifact not found'),
        ]
    )]
    public function loan(Request $request, int $id): JsonResponse
    {
        try {
            $artifact = RecordArtifact::findOrFail($id);
            $loan = $this->service->loanArtifact($artifact, $request->all());
            return response()->json(['success' => true, 'data' => $loan], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/artifacts/{id}/return',
        summary: 'Return artifact from loan',
        security: [['sanctum' => []]],
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Artifact returned successfully'),
            new OA\Response(response: 404, description: 'Artifact not found'),
        ]
    )]
    public function returnFromLoan(int $id): JsonResponse
    {
        try {
            $artifact = RecordArtifact::findOrFail($id);
            $loan = $this->service->returnFromLoan($artifact);
            return response()->json(['success' => true, 'data' => $loan]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/artifacts/{id}/condition-report',
        summary: 'Add condition report for artifact',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'condition', type: 'string', enum: ['excellent', 'good', 'fair', 'poor']),
                    new OA\Property(property: 'notes', type: 'string'),
                    new OA\Property(property: 'report_date', type: 'string', format: 'date'),
                ]
            )
        ),
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Condition report added successfully'),
            new OA\Response(response: 404, description: 'Artifact not found'),
        ]
    )]
    public function addConditionReport(Request $request, int $id): JsonResponse
    {
        try {
            $artifact = RecordArtifact::findOrFail($id);
            $report = $this->service->addConditionReport($artifact, $request->all(), Auth::user());
            return response()->json(['success' => true, 'data' => $report], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Put(
        path: '/api/v1/artifacts/{id}/valuation',
        summary: 'Update artifact valuation',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'estimated_value', type: 'number', format: 'float'),
                    new OA\Property(property: 'insurance_value', type: 'number', format: 'float'),
                    new OA\Property(property: 'valuation_date', type: 'string', format: 'date'),
                ]
            )
        ),
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Valuation updated successfully'),
            new OA\Response(response: 404, description: 'Artifact not found'),
        ]
    )]
    public function updateValuation(Request $request, int $id): JsonResponse
    {
        try {
            $artifact = RecordArtifact::findOrFail($id);
            $updated = $this->service->updateValuation(
                $artifact,
                $request->estimated_value,
                $request->insurance_value,
                $request->valuation_date
            );
            return response()->json(['success' => true, 'data' => $updated]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/artifacts/search',
        summary: 'Advanced search for artifacts',
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'artist', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'category', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'organisation_id', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Search results returned'),
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        try {
            $results = $this->service->searchArtifacts($request->all());
            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/artifacts/statistics',
        summary: 'Get artifact statistics',
        tags: ['Artifacts'],
        parameters: [
            new OA\Parameter(name: 'organisation_id', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Statistics retrieved successfully'),
        ]
    )]
    public function statistics(Request $request): JsonResponse
    {
        try {
            $stats = $this->service->getStatistics($request->get('organisation_id'));
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
