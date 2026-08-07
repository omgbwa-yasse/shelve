<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MailResource;
use App\Http\Resources\Api\V1\RecordResource;
use App\Http\Resources\Api\V1\SlipResource;
use App\Models\Mail;
use App\Models\Record;
use App\Models\Slip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D10 — Recherche libre unifiée, porté le 2026-08-05 contre `SearchController` (Blade).
 *
 * Chaque action est org-scopée (R03) : les notices par `organisation_id`, les courriers
 * et les bordereaux par leur(s) clé(s) d'organisation. Les super-admins voient tout,
 * comme le back-office Blade.
 */
class SearchController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/v1/search/records?q=...
     */
    public function records(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Record::class);

        $query = Record::query()
            ->currentVersion()
            ->where('organisation_id', Auth::user()->current_organisation_id)
            ->with(['type', 'level', 'status', 'activity', 'organisation']);

        foreach ($this->terms($request) as $term) {
            $query->where(function (Builder $builder) use ($term) {
                // `content` (comme les autres champs descriptifs) vit désormais dans
                // `metadata` (JSON) plutôt qu'en colonne directe sur `records`.
                $builder->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('code', 'LIKE', "%{$term}%")
                    ->orWhereRaw('CAST(metadata AS CHAR) LIKE ?', ["%{$term}%"])
                    ->orWhere('description', 'LIKE', "%{$term}%");
            });
        }

        $page = $query->orderBy('updated_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, RecordResource::class));
    }

    /**
     * GET /api/v1/search/mails?q=...&categ=dates|typology|author|container&id=...
     */
    public function mails(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Mail::class);

        $query = Mail::query()->excludeFactoryLike();
        $this->scopeOrganisation($query);

        $terms = $this->terms($request);

        switch ($request->input('categ')) {
            case 'dates':
                foreach ($terms as $term) {
                    $query->where('date', 'LIKE', "%{$term}%");
                }
                break;

            case 'typology':
                if ($request->filled('id')) {
                    $query->where('typology_id', $request->input('id'));
                }
                break;

            case 'author':
                if ($request->filled('id')) {
                    $query->whereHas('authors', fn (Builder $q) => $q->where('authors.id', $request->input('id')));
                }
                break;

            case 'container':
                if ($request->filled('id')) {
                    $query->whereHas('containers', fn (Builder $q) => $q->where('mail_containers.id', $request->input('id')));
                }
                break;

            default:
                foreach ($terms as $term) {
                    $query->where(function (Builder $builder) use ($term) {
                        $builder->where('code', 'LIKE', "%{$term}%")
                            ->orWhere('name', 'LIKE', "%{$term}%")
                            ->orWhere('description', 'LIKE', "%{$term}%");
                    });
                }
                break;
        }

        $page = $query->orderBy('created_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, MailResource::class));
    }

    /**
     * GET /api/v1/search/slips?q=...&advanced=1
     */
    public function slips(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Slip::class);

        $query = Slip::query();

        // Les bordereaux sont org-scopés par double organisation (émetteur/bénéficiaire).
        if (!Auth::user()->isSuperAdmin()) {
            $query->forOrganisation(Auth::user()->current_organisation_id);
        }

        $terms = $this->terms($request);

        if ($request->boolean('advanced')) {
            foreach ($terms as $term) {
                $query->where('name', 'LIKE', "%{$term}%");
            }
        } else {
            foreach ($terms as $term) {
                $query->where(function (Builder $builder) use ($term) {
                    $builder->where('name', 'LIKE', "%{$term}%")
                        ->orWhereHas('officer', fn (Builder $q) => $q->where('name', 'LIKE', "%{$term}%"))
                        ->orWhereHas('user', fn (Builder $q) => $q->where('name', 'LIKE', "%{$term}%"));
                });
            }
        }

        $page = $query->orderBy('created_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, SlipResource::class));
    }

    /**
     * Restreint la requête à l'organisation courante, sauf super-admin (R03).
     */
    private function scopeOrganisation(Builder $query): void
    {
        if (!Auth::user()->isSuperAdmin()) {
            $query->inOrganisation(Auth::user()->current_organisation_id);
        }
    }

    /**
     * Découpe le paramètre de recherche libre (`q` ou `query`) en termes.
     */
    private function terms(Request $request): array
    {
        $q = trim((string) $request->input('q', $request->input('query', '')));

        return $q === '' ? [] : preg_split('/[+\s]+/', $q, -1, PREG_SPLIT_NO_EMPTY);
    }
}
