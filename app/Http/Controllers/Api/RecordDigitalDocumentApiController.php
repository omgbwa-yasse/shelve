<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecordDigitalDocumentService;
use App\Services\MetadataValidationService;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolder;
use App\Models\Organisation;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

/**
 * API Controller for Digital Documents (Phase 9.2)
 *
 * RESTful API endpoints for managing digital documents
 */
#[OA\Tag(name: 'Digital Documents', description: 'Endpoints for managing digital documents')]
class RecordDigitalDocumentApiController extends Controller
{
    protected RecordDigitalDocumentService $service;
    protected MetadataValidationService $metadataValidator;

    public function __construct(RecordDigitalDocumentService $service, MetadataValidationService $metadataValidator)
    {
        $this->service = $service;
        $this->metadataValidator = $metadataValidator;
    }

    /**
     * Get all documents with filtering and pagination
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/digital-documents',
        summary: 'List all digital documents with filters',
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'folder_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'type_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['draft', 'pending', 'approved', 'archived'])),
            new OA\Parameter(name: 'is_current_version', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $query = RecordDigitalDocument::query()
                ->with(['type', 'folder', 'creator', 'organisation', 'attachment']);

            // Filters
            if ($request->has('folder_id')) {
                $query->where('folder_id', $request->folder_id);
            }

            if ($request->has('type_id')) {
                $query->where('type_id', $request->type_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('is_current_version')) {
                $query->where('is_current_version', $request->boolean('is_current_version'));
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $documents = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $documents->items(),
                'pagination' => [
                    'current_page' => $documents->currentPage(),
                    'per_page' => $documents->perPage(),
                    'total' => $documents->total(),
                    'last_page' => $documents->lastPage(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving documents',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific document
     *
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/digital-documents/{id}',
        summary: 'Get a specific digital document',
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Success'),
            new OA\Response(response: 404, description: 'Document not found'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $document = RecordDigitalDocument::with([
                'type',
                'folder',
                'creator',
                'organisation',
                'attachment',
                'assignedUser',
                'approver'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $document,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create a new document
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/v1/digital-documents',
        summary: 'Create a new digital document',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'type_id', 'folder_id', 'organisation_id'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', maxLength: 255),
                        new OA\Property(property: 'description', type: 'string'),
                        new OA\Property(property: 'type_id', type: 'integer'),
                        new OA\Property(property: 'folder_id', type: 'integer'),
                        new OA\Property(property: 'organisation_id', type: 'integer'),
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'File upload (max 50MB)'),
                        new OA\Property(property: 'access_level', type: 'string', enum: ['public', 'internal', 'confidential', 'secret']),
                        new OA\Property(property: 'status', type: 'string', enum: ['draft', 'pending_approval', 'approved', 'rejected', 'archived']),
                        new OA\Property(property: 'assigned_to', type: 'integer'),
                        new OA\Property(property: 'document_date', type: 'string', format: 'date'),
                        new OA\Property(property: 'metadata', type: 'object'),
                    ]
                )
            )
        ),
        tags: ['Digital Documents'],
        responses: [
            new OA\Response(response: 201, description: 'Document created successfully'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type_id' => 'required|exists:record_digital_document_types,id',
            'folder_id' => 'required|exists:record_digital_folders,id',
            'organisation_id' => 'required|exists:organisations,id',
            'file' => 'nullable|file|max:51200', // 50MB max
            'access_level' => 'nullable|in:public,internal,confidential,secret',
            'status' => 'nullable|in:draft,pending_approval,approved,rejected,archived',
            'assigned_to' => 'nullable|exists:users,id',
            'document_date' => 'nullable|date',
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
            $folder = RecordDigitalFolder::findOrFail($request->folder_id);
            $type = RecordDigitalDocumentType::findOrFail($request->type_id);

            // Validate metadata according to document type profile
            $metadata = $request->metadata ?? [];
            if (!empty($metadata)) {
                try {
                    $metadata = $this->metadataValidator->validateDocumentMetadata($type, $metadata);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Metadata validation error',
                        'errors' => $e->errors(),
                    ], 422);
                }
            }

            // Handle file upload
            $attachment = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('documents', 'public');

                $attachment = Attachment::create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'attachmentable_type' => RecordDigitalDocument::class,
                    'creator_id' => $user->id,
                ]);
            }

            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'metadata' => $metadata,
                'access_level' => $request->access_level ?? null,
                'status' => $request->status ?? 'draft',
                'assigned_to' => $request->assigned_to ?? null,
                'document_date' => $request->document_date ?? now(),
            ];

            $document = $this->service->createDocument(
                $type,
                $folder,
                $data,
                $user,
                $organisation,
                $attachment
            );

            // Update attachment with document ID
            if ($attachment) {
                $attachment->update(['attachmentable_id' => $document->id]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Document created successfully',
                'data' => $document->load(['type', 'folder', 'attachment']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a document
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Put(
        path: '/api/v1/digital-documents/{id}',
        summary: 'Update an existing digital document',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', enum: ['draft', 'pending_approval', 'approved', 'rejected', 'archived']),
                    new OA\Property(property: 'access_level', type: 'string', enum: ['public', 'internal', 'confidential', 'secret']),
                    new OA\Property(property: 'assigned_to', type: 'integer'),
                    new OA\Property(property: 'metadata', type: 'object'),
                ]
            )
        ),
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Document updated successfully'),
            new OA\Response(response: 404, description: 'Document not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:draft,pending_approval,approved,rejected,archived',
            'access_level' => 'nullable|in:public,internal,confidential,secret',
            'assigned_to' => 'nullable|exists:users,id',
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
            $document = RecordDigitalDocument::with('type')->findOrFail($id);

            // Validate metadata if provided
            if ($request->has('metadata')) {
                try {
                    $metadata = $this->metadataValidator->validateDocumentMetadata($document->type, $request->metadata);
                    $document->metadata = $metadata;
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Metadata validation error',
                        'errors' => $e->errors(),
                    ], 422);
                }
            }

            $document->update($request->only([
                'name',
                'description',
                'status',
                'access_level',
                'assigned_to',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully',
                'data' => $document->fresh(['type', 'folder', 'attachment']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a document
     *
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/api/v1/digital-documents/{id}',
        summary: 'Delete a digital document (soft delete)',
        security: [['sanctum' => []]],
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Document deleted successfully'),
            new OA\Response(response: 404, description: 'Document not found'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        try {
            $document = RecordDigitalDocument::findOrFail($id);

            // Soft delete
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new version of a document
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/v1/digital-documents/{id}/versions',
        summary: 'Create a new version of a document',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'New version file (max 50MB)'),
                        new OA\Property(property: 'version_notes', type: 'string'),
                    ]
                )
            )
        ),
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Version created successfully'),
            new OA\Response(response: 404, description: 'Document not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function createVersion(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:51200',
            'version_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $document = RecordDigitalDocument::findOrFail($id);
            $user = Auth::user();

            // Upload new file
            $file = $request->file('file');
            $path = $file->store('documents', 'public');

            $attachment = Attachment::create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'attachmentable_type' => RecordDigitalDocument::class,
                'creator_id' => $user->id,
            ]);

            // Create new version
            $newVersion = $this->service->createVersion(
                $document,
                $attachment,
                $user,
                $request->version_notes
            );

            // Update attachment with new document ID
            $attachment->update(['attachmentable_id' => $newVersion->id]);

            return response()->json([
                'success' => true,
                'message' => 'New version created successfully',
                'data' => $newVersion->load(['type', 'folder', 'attachment']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating version',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit document for approval
     *
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/v1/digital-documents/{id}/submit',
        summary: 'Submit document for approval',
        security: [['sanctum' => []]],
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Document submitted for approval'),
            new OA\Response(response: 400, description: 'Only draft documents can be submitted'),
            new OA\Response(response: 404, description: 'Document not found'),
        ]
    )]
    public function submitForApproval(int $id): JsonResponse
    {
        try {
            $document = RecordDigitalDocument::findOrFail($id);

            if ($document->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft documents can be submitted for approval',
                ], 400);
            }

            $document->update(['status' => 'pending_approval']);

            return response()->json([
                'success' => true,
                'message' => 'Document submitted for approval',
                'data' => $document->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve a document
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/v1/digital-documents/{id}/approve',
        summary: 'Approve a document',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'approval_notes', type: 'string'),
                ]
            )
        ),
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Document approved successfully'),
            new OA\Response(response: 404, description: 'Document not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function approve(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $document = RecordDigitalDocument::findOrFail($id);
            $user = Auth::user();

            $approvedDocument = $this->service->approveDocument(
                $document,
                $user,
                $request->approval_notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Document approved successfully',
                'data' => $approvedDocument->fresh(['approver']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a document
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Post(
        path: '/api/v1/digital-documents/{id}/reject',
        summary: 'Reject a document',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['rejection_notes'],
                properties: [
                    new OA\Property(property: 'rejection_notes', type: 'string'),
                ]
            )
        ),
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Document rejected successfully'),
            new OA\Response(response: 404, description: 'Document not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function reject(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rejection_notes' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $document = RecordDigitalDocument::findOrFail($id);

            $document->update([
                'status' => 'rejected',
                'approval_notes' => $request->rejection_notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document rejected',
                'data' => $document->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search documents
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/digital-documents/search',
        summary: 'Advanced search for documents',
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'code', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'type_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'folder_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'creator_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'organisation_id', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date_from', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Search results returned'),
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        try {
            $criteria = $request->only([
                'name',
                'code',
                'type_id',
                'folder_id',
                'status',
                'creator_id',
                'organisation_id',
                'date_from',
                'date_to',
            ]);

            $results = $this->service->searchDocuments($criteria);

            return response()->json([
                'success' => true,
                'data' => $results,
                'count' => $results->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching documents',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a document file
     *
     * @param int $id
     * @return mixed
     */
    #[OA\Get(
        path: '/api/v1/digital-documents/{id}/download',
        summary: 'Download document file',
        security: [['sanctum' => []]],
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'File download started'),
            new OA\Response(response: 404, description: 'Document or file not found'),
        ]
    )]
    public function download(int $id)
    {
        try {
            $document = RecordDigitalDocument::with('attachment')->findOrFail($id);

            if (!$document->attachment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file attached to this document',
                ], 404);
            }

            // Increment download count
            $document->increment('download_count');

            $path = storage_path('app/public/' . $document->attachment->path);

            return response()->download($path, $document->attachment->name);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get document versions
     *
     * @param int $id
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/v1/digital-documents/{id}/versions',
        summary: 'Get all versions of a document',
        tags: ['Digital Documents'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Versions retrieved successfully'),
            new OA\Response(response: 404, description: 'Document not found'),
        ]
    )]
    public function versions(int $id): JsonResponse
    {
        try {
            $document = RecordDigitalDocument::findOrFail($id);

            // Get all versions (same code, different version_number)
            $versions = RecordDigitalDocument::where('code', $document->code)
                ->orderBy('version_number', 'desc')
                ->with(['creator', 'attachment'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $versions,
                'count' => $versions->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving versions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
