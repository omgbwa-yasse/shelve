<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecordPeriodicService;
use App\Models\RecordPeriodic;
use App\Models\RecordPeriodicIssue;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Periodicals', description: 'Endpoints for managing periodicals, journals, magazines, and newspapers')]
class RecordPeriodicApiController extends Controller
{
    protected RecordPeriodicService $service;

    public function __construct(RecordPeriodicService $service)
    {
        $this->service = $service;
    }

    #[OA\Get(
        path: '/api/v1/periodicals',
        summary: 'List all periodicals with filters',
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string', enum: ['journal', 'magazine', 'newspaper', 'bulletin'])),
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
            $query = RecordPeriodic::query()->with(['creator', 'organisation']);

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('issn', 'like', "%{$search}%");
                });
            }

            $periodics = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $periodics->items(),
                'pagination' => [
                    'current_page' => $periodics->currentPage(),
                    'total' => $periodics->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/periodicals/{id}',
        summary: 'Get a specific periodical',
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 404, description: 'Periodical not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $periodic = RecordPeriodic::with(['creator', 'organisation', 'issues', 'subscriptions'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $periodic]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }
    }

    #[OA\Post(
        path: '/api/v1/periodicals',
        summary: 'Create a new periodical',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'organisation_id', 'type'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 255),
                    new OA\Property(property: 'organisation_id', type: 'integer'),
                    new OA\Property(property: 'type', type: 'string', enum: ['journal', 'magazine', 'newspaper', 'bulletin']),
                    new OA\Property(property: 'issn', type: 'string'),
                    new OA\Property(property: 'publisher', type: 'string'),
                    new OA\Property(property: 'frequency', type: 'string', enum: ['daily', 'weekly', 'monthly', 'quarterly', 'annual']),
                    new OA\Property(property: 'description', type: 'string'),
                ]
            )
        ),
        tags: ['Periodicals'],
        responses: [
            new OA\Response(response: 201, description: 'Periodical created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'organisation_id' => 'required|exists:organisations,id',
            'type' => 'required|in:journal,magazine,newspaper,bulletin',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $organisation = Organisation::findOrFail($request->organisation_id);
            $periodic = $this->service->createPeriodic($request->all(), Auth::user(), $organisation);
            return response()->json(['success' => true, 'data' => $periodic], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Put(
        path: '/api/v1/periodicals/{id}',
        summary: 'Update an existing periodical',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'publisher', type: 'string'),
                    new OA\Property(property: 'frequency', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                ]
            )
        ),
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Periodical updated successfully'),
            new OA\Response(response: 404, description: 'Periodical not found'),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $periodic = RecordPeriodic::findOrFail($id);
            $periodic->update($request->all());
            return response()->json(['success' => true, 'data' => $periodic->fresh()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Delete(
        path: '/api/v1/periodicals/{id}',
        summary: 'Delete a periodical',
        security: [['sanctum' => []]],
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Periodical deleted successfully'),
            new OA\Response(response: 404, description: 'Periodical not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            RecordPeriodic::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/periodicals/{id}/issues',
        summary: 'Add an issue to a periodical',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'issue_number', type: 'string'),
                    new OA\Property(property: 'volume', type: 'string'),
                    new OA\Property(property: 'publication_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'pages', type: 'integer'),
                ]
            )
        ),
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Issue added successfully'),
            new OA\Response(response: 404, description: 'Periodical not found'),
        ]
    )]
    public function addIssue(Request $request, int $id): JsonResponse
    {
        try {
            $periodic = RecordPeriodic::findOrFail($id);
            $issue = $this->service->addIssue($periodic, $request->all());
            return response()->json(['success' => true, 'data' => $issue], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/periodicals/issues/{issueId}/articles',
        summary: 'Add an article to an issue',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'author', type: 'string'),
                    new OA\Property(property: 'pages', type: 'string'),
                    new OA\Property(property: 'abstract', type: 'string'),
                ]
            )
        ),
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'issueId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Article added successfully'),
            new OA\Response(response: 404, description: 'Issue not found'),
        ]
    )]
    public function addArticle(Request $request, int $issueId): JsonResponse
    {
        try {
            $issue = RecordPeriodicIssue::findOrFail($issueId);
            $article = $this->service->addArticle($issue, $request->all());
            return response()->json(['success' => true, 'data' => $article], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: '/api/v1/periodicals/{id}/subscriptions',
        summary: 'Create a subscription to a periodical',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'user_id', type: 'integer', description: 'User ID (optional, defaults to authenticated user)'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'cost', type: 'number', format: 'float'),
                ]
            )
        ),
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Subscription created successfully'),
            new OA\Response(response: 404, description: 'Periodical not found'),
        ]
    )]
    public function createSubscription(Request $request, int $id): JsonResponse
    {
        try {
            $periodic = RecordPeriodic::findOrFail($id);
            $user = $request->has('user_id') ? User::findOrFail($request->user_id) : Auth::user();
            $subscription = $this->service->createSubscription($periodic, $request->all(), $user);
            return response()->json(['success' => true, 'data' => $subscription], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/periodicals/search',
        summary: 'Search periodicals',
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'title', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'issn', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'publisher', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Search results returned'),
        ]
    )]
    public function searchPeriodics(Request $request): JsonResponse
    {
        try {
            $results = $this->service->searchPeriodics($request->all());
            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/periodicals/issues/search',
        summary: 'Search issues',
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'periodical_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'issue_number', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'volume', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'date_from', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Search results returned'),
        ]
    )]
    public function searchIssues(Request $request): JsonResponse
    {
        try {
            $results = $this->service->searchIssues($request->all());
            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/periodicals/articles/search',
        summary: 'Search articles',
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'title', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'author', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'issue_id', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Search results returned'),
        ]
    )]
    public function searchArticles(Request $request): JsonResponse
    {
        try {
            $results = $this->service->searchArticles($request->all());
            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/periodicals/subscriptions/expiring',
        summary: 'Get subscriptions expiring soon',
        tags: ['Periodicals'],
        parameters: [
            new OA\Parameter(name: 'days', in: 'query', schema: new OA\Schema(type: 'integer', default: 30)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Expiring subscriptions returned'),
        ]
    )]
    public function expiringSoonSubscriptions(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 30);
            $results = $this->service->getExpiringSoonSubscriptions($days);
            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/periodicals/issues/missing',
        summary: 'Get list of missing issues',
        tags: ['Periodicals'],
        responses: [
            new OA\Response(response: 200, description: 'Missing issues returned'),
        ]
    )]
    public function missingIssues(): JsonResponse
    {
        try {
            $results = $this->service->getMissingIssues();
            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/periodicals/statistics',
        summary: 'Get periodical statistics',
        tags: ['Periodicals'],
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
