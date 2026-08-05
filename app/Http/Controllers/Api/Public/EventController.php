<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Public\StoreEventRegistrationRequest;
use App\Http\Resources\Api\Public\EventRegistrationResource;
use App\Http\Resources\Api\Public\EventResource;
use App\Models\PublicEvent;
use App\Models\PublicEventRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D15 — événements du portail public. Lecture publique (guard public) ;
 * l'inscription et l'annulation sont réservées aux usagers connectés
 * (`auth:sanctum`, modèle PublicUser).
 */
class EventController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/public/events — événements paginés, filtre temporel comme le
     * contrôleur Blade OPAC\EventController (défaut : à venir).
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'filter' => 'nullable|string|in:upcoming,past,today,this_week,this_month',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'is_online' => 'nullable|boolean',
            'sort' => 'nullable|string|in:start_date,-start_date',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = PublicEvent::query();

        match ($request->get('filter', 'upcoming')) {
            'past' => $query->where('end_date', '<', now()->startOfDay()),
            'today' => $query->whereDate('start_date', '<=', now())->whereDate('end_date', '>=', now()),
            'this_week' => $query->whereBetween('start_date', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereBetween('start_date', [now()->startOfMonth(), now()->endOfMonth()]),
            default => $query->where('start_date', '>=', now()->startOfDay()),
        };

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date('date_to'));
        }

        if ($request->filled('is_online')) {
            $query->where('is_online', $request->boolean('is_online'));
        }

        $query->orderBy('start_date', $request->get('sort') === '-start_date' ? 'desc' : 'asc');

        $page = $query->withCount('registrations')
            ->paginate(min((int) $request->get('per_page', 10), 50))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, EventResource::class));
    }

    /**
     * GET /api/public/events/{event} — détail d'un événement (compteur public
     * d'inscriptions, aucune donnée nominative).
     */
    public function show(PublicEvent $event): JsonResponse
    {
        $event->loadCount('registrations');

        return response()->json(['data' => new EventResource($event)]);
    }

    /**
     * POST /api/public/events/{event}/registrations — inscription de l'usager
     * connecté. `user_id` vient du token, jamais du corps de la requête.
     */
    public function register(StoreEventRegistrationRequest $request, PublicEvent $event): JsonResponse
    {
        $user = $request->user();

        if (PublicEventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists()) {
            return response()->json([
                'type' => 'about:blank',
                'title' => 'Conflit',
                'status' => 409,
                'detail' => 'L\'usager est déjà inscrit à cet événement.',
            ], 409);
        }

        $registration = PublicEventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'registered',
            // `registered_at` n'est pas dans $fillable : le défaut de la table
            // (CURRENT_TIMESTAMP) le renseigne à la création.
            'notes' => $request->validated('notes'),
        ]);

        return response()->json(
            ['data' => new EventRegistrationResource($registration)],
            201,
            ['Location' => "/api/public/events/{$event->id}/registrations"]
        );
    }

    /**
     * GET /api/public/events/{event}/registrations — inscription de l'usager
     * courant sur cet événement (404 s'il n'est pas inscrit).
     */
    public function registration(Request $request, PublicEvent $event): JsonResponse
    {
        $registration = PublicEventRegistration::where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->first();

        abort_if(!$registration, 404);

        return response()->json(['data' => new EventRegistrationResource($registration)]);
    }

    /**
     * DELETE /api/public/events/{event}/registrations — annulation de sa propre
     * inscription.
     */
    public function cancelRegistration(Request $request, PublicEvent $event): Response
    {
        $registration = PublicEventRegistration::where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->first();

        abort_if(!$registration, 404);

        $registration->delete();

        return response()->noContent();
    }
}
