<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecordDigitalFolderService;
use App\Services\MetadataValidationService;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalFolderType;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

/**
 * API Controller for Digital Folders (Phase 9.1)
 *
 * RESTful API endpoints for managing digital folders
 */
#[OA\Tag(name: 'Digital Folders', description: 'Endpoints for managing digital folders')]
class RecordDigitalFolderApiController extends Controller
{
    protected RecordDigitalFolderService $service;
    protected MetadataValidationService $metadataValidator;

    public function __construct(RecordDigitalFolderService $service, MetadataValidationService $metadataValidator)
    {
        $this->service = $service;
        $this->metadataValidator = $metadataValidator;
    }

    #[OA\Get(
        path: '/api/v1/digital-folders',
        summary: 'List all digital folders',
        tags: ['Digital Folders'],
        parameters: [
            new OA\Parameter(name: 'name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'path', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'parent_id', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['name', 'path', 'status', 'parent_id']);
            $folders = $this->service->searchFolders($filters);

            return response()->json([
                'success' => true,
                'data' => $folders,
                'count' => $folders->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving folders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    #[OA\Get(
        path: '/api/v1/digital-folders/{id}',
        summary: 'Get a specific digital folder',
        tags: ['Digital Folders'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 404, description: 'Folder not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $folder = RecordDigitalFolder::with(['parent', 'children', 'creator', 'organisation'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $folder,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Folder not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    #[OA\Post(
        path: '/api/v1/digital-folders',
        summary: 'Create a new digital folder',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'type_id', 'organisation_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'type_id', type: 'integer'),
                    new OA\Property(property: 'parent_id', type: 'integer'),
                    new OA\Property(property: 'organisation_id', type: 'integer'),
                    new OA\Property(property: 'access_level', type: 'string', enum: ['public', 'internal', 'confidential', 'secret']),
                    new OA\Property(property: 'assigned_to', type: 'integer'),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'end_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'metadata', type: 'object'),
                ]
            )
        ),
        tags: ['Digital Folders'],
        responses: [
            new OA\Response(response: 201, description: 'Folder created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_id' => 'required|exists:record_digital_folder_types,id',
            'parent_id' => 'nullable|exists:record_digital_folders,id',
            'organisation_id' => 'required|exists:organisations,id',
            'access_level' => 'nullable|in:public,internal,confidential,secret',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $organisation = Organisation::findOrFail($request->organisation_id);
            $parent = $request->parent_id ? RecordDigitalFolder::findOrFail($request->parent_id) : null;
            $type = RecordDigitalFolderType::findOrFail($request->type_id);

            // Validate metadata according to folder type profile
            $metadata = $request->metadata ?? [];
            if (!empty($metadata)) {
                try {
                    $metadata = $this->metadataValidator->validateFolderMetadata($type, $metadata);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Metadata validation error',
                        'errors' => $e->errors(),
                    ], 422);
                }
            }

            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'metadata' => $metadata,
                'access_level' => $request->access_level ?? null,
                'assigned_to' => $request->assigned_to ?? null,
                'start_date' => $request->start_date ?? null,
                'end_date' => $request->end_date ?? null,
            ];

            $folder = $this->service->createFolder(
                $type,
                $data,
                $user,
                $organisation,
                $parent
            );

            return response()->json([
                'success' => true,
                'message' => 'Folder created successfully',
                'data' => $folder,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating folder',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a folder
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/v1/digital-folders/{id}',
        summary: 'Update an existing digital folder',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'archived', 'deleted']),
                    new OA\Property(property: 'metadata', type: 'object'),
                ]
            )
        ),
        tags: ['Digital Folders'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Folder updated successfully'),
            new OA\Response(response: 404, description: 'Folder not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,archived,deleted',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $folder = RecordDigitalFolder::with('type')->findOrFail($id);

            // Validate metadata if provided
            if ($request->has('metadata')) {
                try {
                    $metadata = $this->metadataValidator->validateFolderMetadata($folder->type, $request->metadata);
                    $folder->metadata = $metadata;
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Metadata validation error',
                        'errors' => $e->errors(),
                    ], 422);
                }
            }

            if ($request->has('name')) {
                $folder = $this->service->renameFolder($folder, $request->name);
            }

            $folder->update($request->only(['description', 'status']));

            return response()->json([
                'success' => true,
                'message' => 'Folder updated successfully',
                'data' => $folder->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating folder',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a folder
     *
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/v1/digital-folders/{id}',
        summary: 'Delete a digital folder',
        security: [['sanctum' => []]],
        tags: ['Digital Folders'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Folder deleted successfully'),
            new OA\Response(response: 404, description: 'Folder not found'),
            new OA\Response(response: 500, description: 'Error deleting folder'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $folder = RecordDigitalFolder::findOrFail($id);
            $this->service->deleteFolder($folder);

            return response()->json([
                'success' => true,
                'message' => 'Folder deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting folder',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get folder tree structure
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/digital-folders/{id}/tree',
        summary: 'Get folder tree structure',
        tags: ['Digital Folders'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Folder ID to get tree for, or omit to get all root folders',
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'parent_id',
                in: 'query',
                description: 'Alternative way to specify parent folder',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tree structure returned successfully'),
            new OA\Response(response: 404, description: 'Folder not found'),
        ]
    )]
    public function tree(Request $request): JsonResponse
    {
        try {
            $parentId = $request->query('parent_id');

            // If parent_id provided, get that folder's tree, otherwise get all root folders
            if ($parentId) {
                $parent = RecordDigitalFolder::findOrFail($parentId);
                $tree = $this->service->getFolderTree($parent);
            } else {
                // Get all root folders with their trees
                $roots = RecordDigitalFolder::roots()->with('children')->get();
                $tree = $roots->map(function($root) {
                    return $this->service->getFolderTree($root);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $tree,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving folder tree',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Move folder to new parent
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/v1/digital-folders/{id}/move',
        summary: 'Move folder to a new parent',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'parent_id',
                        type: 'integer',
                        description: 'New parent folder ID, or null to move to root',
                        nullable: true
                    ),
                ]
            )
        ),
        tags: ['Digital Folders'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Folder moved successfully'),
            new OA\Response(response: 404, description: 'Folder not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function move(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:record_digital_folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $folder = RecordDigitalFolder::findOrFail($id);
            $newParent = $request->parent_id ? RecordDigitalFolder::findOrFail($request->parent_id) : null;

            $folder = $this->service->moveFolder($folder, $newParent);

            return response()->json([
                'success' => true,
                'message' => 'Folder moved successfully',
                'data' => $folder->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error moving folder',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get folder statistics
     *
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/digital-folders/{id}/statistics',
        summary: 'Get folder statistics (size, document count, etc.)',
        tags: ['Digital Folders'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistics retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'total_size', type: 'integer'),
                                new OA\Property(property: 'document_count', type: 'integer'),
                                new OA\Property(property: 'subfolder_count', type: 'integer'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Folder not found'),
        ]
    )]
    public function statistics(int $id): JsonResponse
    {
        try {
            $folder = RecordDigitalFolder::findOrFail($id);
            $stats = $this->service->getFolderStatistics($folder);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get folder ancestors (breadcrumb)
     *
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/digital-folders/{id}/ancestors',
        summary: 'Get folder ancestors (breadcrumb trail)',
        tags: ['Digital Folders'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ancestors retrieved successfully'),
            new OA\Response(response: 404, description: 'Folder not found'),
        ]
    )]
    public function ancestors(int $id): JsonResponse
    {
        try {
            $folder = RecordDigitalFolder::findOrFail($id);
            $ancestors = $folder->getAncestors();

            return response()->json([
                'success' => true,
                'data' => $ancestors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving ancestors',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get root folders
     *
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/digital-folders/roots',
        summary: 'Get all root-level folders',
        tags: ['Digital Folders'],
        responses: [
            new OA\Response(response: 200, description: 'Root folders retrieved successfully'),
        ]
    )]
    public function roots(): JsonResponse
    {
        try {
            $roots = RecordDigitalFolder::roots()
                ->with(['type', 'creator', 'organisation'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $roots,
                'count' => $roots->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving root folders',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
