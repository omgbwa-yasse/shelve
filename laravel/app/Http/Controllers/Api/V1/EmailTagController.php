<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EmailTagResource;
use App\Models\EmailTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class EmailTagController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', EmailTag::class);

        $tags = EmailTag::byOrganisation(Auth::user()->current_organisation_id)
            ->withCount('messages')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => EmailTagResource::collection($tags)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', EmailTag::class);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);
        $data['created_by'] = Auth::id();

        $tag = EmailTag::create($data);

        return response()->json(['data' => new EmailTagResource($tag)], 201);
    }

    public function update(Request $request, EmailTag $emailTag): JsonResponse
    {
        $this->authorize('update', $emailTag);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $emailTag->update($data);

        return response()->json(['data' => new EmailTagResource($emailTag->fresh())]);
    }

    public function destroy(EmailTag $emailTag): Response
    {
        $this->authorize('delete', $emailTag);

        $emailTag->delete();

        return response()->noContent();
    }
}
