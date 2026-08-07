<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Dolly;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D11 — handler JSON de chariots (`dolly-handler/*`), relu le 2026-08-04 contre
 * `DollyHandlerController` (Blade) et le schéma.
 *
 * Ces routes sont consommées telles quelles par l'interface chariot : les formes de
 * réponse du Blade sont conservées. L'accès à un chariot d'une autre organisation
 * renvoie **404** (le Blade renvoyait 403 — voir CONVENTIONS §4 : ne jamais confirmer
 * l'existence d'une ressource hors périmètre).
 *
 * Catégories supportées : mail, communication, building, room, record, slip,
 * container, shelf, digital_folder, digital_document (la relation « shelves » du
 * modèle est `shelve()`). TODO : la catégorie `transferring` n'a pas de relation.
 */
class DollyHandlerController extends Controller
{
    /** Relation Eloquent du Dolly pour chaque catégorie supportée. */
    private const CATEGORY_RELATION = [
        'mail' => 'mails',
        'communication' => 'communications',
        'building' => 'buildings',
        'room' => 'rooms',
        'record' => 'records',
        'slip' => 'slips',
        'container' => 'containers',
        'shelf' => 'shelve',
        'digital_folder' => 'digitalFolders',
        'digital_document' => 'digitalDocuments',
    ];

    private const LIST_CATEGORIES = 'mail,communication,building,transferring,room,record,slip,container,shelf,digital_folder,digital_document';

    private function dollyOfOrg(int $dollyId): ?Dolly
    {
        return Dolly::where('id', $dollyId)
            ->where('owner_organisation_id', Auth::user()->current_organisation_id)
            ->first();
    }

    /**
     * GET /api/v1/dolly-handler/list
     */
    public function list(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Dolly::class);

        $request->validate([
            'category' => 'required|string|in:' . self::LIST_CATEGORIES,
        ]);

        $dollies = Dolly::where('category', $request->input('category'))
            ->where(function ($query) {
                $query->where('owner_organisation_id', Auth::user()->current_organisation_id)
                    ->orWhere('is_public', true);
            })
            ->get();

        $relation = self::CATEGORY_RELATION[$request->input('category')] ?? null;

        if ($relation && $dollies->isNotEmpty()) {
            $dollies->load($relation);
        }

        return response()->json(['dollies' => $dollies], 200);
    }

    /**
     * POST /api/v1/dolly-handler/create
     */
    public function addDolly(Request $request): JsonResponse
    {
        $this->authorize('create', Dolly::class);

        $request->validate([
            'name' => 'required|string|max:70',
            'description' => 'nullable|string|max:100',
            'category' => 'required|string|in:mail,transaction,record,slip,building,shelf,container,communication,room,digital_folder,digital_document',
        ]);

        $dolly = Dolly::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'is_public' => false,
            'created_by' => Auth::id(),
            'owner_organisation_id' => Auth::user()->current_organisation_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dolly created successfully',
            'data' => $dolly,
        ], 201);
    }

    /**
     * POST /api/v1/dolly-handler/add-items
     */
    public function addItems(Request $request): JsonResponse
    {
        $request->validate([
            'dolly_id' => 'required|integer|exists:dollies,id',
            'category' => 'required|string|in:' . self::LIST_CATEGORIES,
            'items' => 'required|array',
        ]);

        $dolly = $this->dollyOfOrg((int) $request->input('dolly_id'));

        if (!$dolly) {
            return response()->json(['message' => 'Dolly not found'], 404);
        }

        $this->authorize('update', $dolly);

        $items = array_map('intval', $request->input('items'));

        switch ($request->input('category')) {
            case 'mail':
                $dolly->mails()->syncWithoutDetaching($items);
                break;
            case 'communication':
                $dolly->communications()->syncWithoutDetaching($items);
                break;
            case 'building':
                $dolly->buildings()->syncWithoutDetaching($items);
                break;
            case 'room':
                $dolly->rooms()->syncWithoutDetaching($items);
                break;
            case 'record':
                $dolly->records()->syncWithoutDetaching($items);
                break;
            case 'slip':
                $dolly->slips()->syncWithoutDetaching($items);
                break;
            case 'shelf':
                $dolly->shelve()->syncWithoutDetaching($items);
                break;
            case 'container':
                $dolly->containers()->syncWithoutDetaching($items);
                break;
            case 'digital_folder':
                $dolly->digitalFolders()->syncWithoutDetaching($items);
                break;
            case 'digital_document':
                $dolly->digitalDocuments()->syncWithoutDetaching($items);
                break;
            default:
                return response()->json(['message' => 'Type non valide'], 400);
        }

        return response()->json(['message' => 'Éléments ajoutés avec succès'], 200);
    }

    /**
     * DELETE /api/v1/dolly-handler/remove-items
     */
    public function removeItems(Request $request): JsonResponse
    {
        $request->validate([
            'dolly_id' => 'required|integer|exists:dollies,id',
            'category' => 'required|string|in:' . self::LIST_CATEGORIES,
            'items' => 'required|array',
        ]);

        $dolly = $this->dollyOfOrg((int) $request->input('dolly_id'));

        if (!$dolly) {
            return response()->json(['message' => 'Dolly not found'], 404);
        }

        $this->authorize('update', $dolly);

        $items = $request->input('items');

        match ($request->input('category')) {
            'mail' => $dolly->mails()->detach($items),
            'communication' => $dolly->communications()->detach($items),
            'building' => $dolly->buildings()->detach($items),
            'room' => $dolly->rooms()->detach($items),
            'record' => $dolly->records()->detach($items),
            'slip' => $dolly->slips()->detach($items),
            'shelf' => $dolly->shelve()->detach($items),
            'container' => $dolly->containers()->detach($items),
            'digital_folder' => $dolly->digitalFolders()->detach($items),
            'digital_document' => $dolly->digitalDocuments()->detach($items),
            default => null,
        };

        return response()->json(['message' => 'Items removed successfully'], 200);
    }

    /**
     * DELETE /api/v1/dolly-handler/clean
     */
    public function clean(Request $request): JsonResponse
    {
        $request->validate([
            'dolly_id' => 'required|integer|exists:dollies,id',
            'category' => 'required|string|in:' . self::LIST_CATEGORIES,
        ]);

        $dolly = $this->dollyOfOrg((int) $request->input('dolly_id'));

        if (!$dolly) {
            return response()->json(['message' => 'Dolly not found'], 404);
        }

        $this->authorize('update', $dolly);

        match ($request->input('category')) {
            'mail' => $dolly->mails()->detach(),
            'communication' => $dolly->communications()->detach(),
            'building' => $dolly->buildings()->detach(),
            'room' => $dolly->rooms()->detach(),
            'record' => $dolly->records()->detach(),
            'slip' => $dolly->slips()->detach(),
            'shelf' => $dolly->shelve()->detach(),
            'container' => $dolly->containers()->detach(),
            'digital_folder' => $dolly->digitalFolders()->detach(),
            'digital_document' => $dolly->digitalDocuments()->detach(),
            default => null,
        };

        return response()->json(['message' => 'Items removed successfully'], 200);
    }

    /**
     * DELETE /api/v1/dolly-handler/{dolly_id}
     */
    public function deleteDolly(int $dolly_id): JsonResponse
    {
        $dolly = $this->dollyOfOrg($dolly_id);

        if (!$dolly) {
            return response()->json(['message' => 'Dolly not found'], 404);
        }

        $this->authorize('delete', $dolly);

        $dolly->mails()->detach();
        $dolly->communications()->detach();
        $dolly->buildings()->detach();
        $dolly->rooms()->detach();
        $dolly->records()->detach();
        $dolly->slips()->detach();
        $dolly->shelve()->detach();
        $dolly->containers()->detach();
        $dolly->digitalFolders()->detach();
        $dolly->digitalDocuments()->detach();
        $dolly->delete();

        return response()->json(['message' => 'Dolly and its relations deleted successfully'], 200);
    }
}
