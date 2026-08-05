<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MailResource;
use App\Http\Resources\Api\V1\MailTransactionResource;
use App\Http\Resources\Api\V1\MailTypologyResource;
use App\Models\Batch;
use App\Models\Mail;
use App\Models\MailContainer;
use App\Models\MailTransaction;
use App\Models\MailTypology;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * D10 — Recherche de courriers, porté le 2026-08-05 contre `SearchMailController`
 * et `SearchMailFeedbackController` (Blade).
 *
 * Recherche avancée org-scopée (`Mail::inOrganisation`, R03), mêmes critères que le
 * back-office : attributs directs, pièces jointes, relations, auteur, archivage,
 * dates, parapheur et conteneur. Le feedback (`mails/feedback`) interroge les
 * transactions de courrier via leur action.
 */
class SearchMailController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/v1/search/mails/advanced?code=&name=&mail_type=&date=&attachment_content=
     *     &priority_id=&typology_id=&container_id=&document_type_id=&author_ids=
     *     &archived=&categ=&date_exact=&date_start=&date_end=&batch_id=&type=
     */
    public function advanced(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Mail::class);

        $query = Mail::query()->excludeFactoryLike();
        $this->scopeOrganisation($query);

        $directFilters = ['code' => 'like', 'name' => 'like', 'mail_type' => 'exact', 'date' => 'date'];

        foreach ($directFilters as $field => $type) {
            if ($request->filled($field)) {
                if ($type === 'like') {
                    $query->where($field, 'like', '%' . $request->input($field) . '%');
                } elseif ($type === 'date') {
                    $query->whereDate($field, $request->input($field));
                } else {
                    $query->where($field, $request->input($field));
                }
            }
        }

        if ($request->filled('attachment_content')) {
            $needle = $request->input('attachment_content');
            $query->whereHas('attachments', fn (Builder $q) => $q->where('attachments.content_text', 'like', '%' . $needle . '%'));
        }

        // Le type de document est une colonne de `mails` (`document_type`), pas une
        // relation : le filtre Blade passait par une relation `documents` inexistante.
        if ($request->filled('document_type_id')) {
            $query->where('document_type', $request->input('document_type_id'));
        }

        $relationFilters = [
            'priority_id' => ['relation' => 'priority', 'foreign_key' => 'id'],
            'typology_id' => ['relation' => 'typology', 'foreign_key' => 'id'],
            'container_id' => ['relation' => 'containers', 'foreign_key' => 'mail_containers.id'],
        ];

        foreach ($relationFilters as $field => $options) {
            if ($request->filled($field)) {
                $query->whereHas($options['relation'], fn (Builder $q) => $q->where($options['foreign_key'], $request->input($field)));
            }
        }

        if ($request->filled('author_ids')) {
            $authorIds = explode(',', (string) $request->input('author_ids'));
            $query->whereHas('authors', fn (Builder $q) => $q->whereIn('authors.id', $authorIds));
        }

        if ($request->filled('archived')) {
            $request->boolean('archived') ? $query->archived() : $query->notArchived();
        }

        if ($request->filled('categ')) {
            switch ($request->input('categ')) {
                case 'dates':
                    $this->applyDateCriteria($query, $request);
                    break;

                case 'batch':
                    $this->applyBatchCriteria($query, $request);
                    break;

                case 'container':
                    $this->applyContainerCriteria($query, $request);
                    break;
            }
        }

        $page = $query->with([
            'archives', 'containers', 'attachments',
            'recipientOrganisation', 'recipient',
            'senderOrganisation', 'sender', 'action',
            'typology', 'priority',
        ])
            ->orderBy('created_at', 'desc')
            ->paginate($this->pageSize($request))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, MailResource::class));
    }

    /**
     * GET /api/v1/search/mails/typologies
     */
    public function typologies(): JsonResponse
    {
        $this->authorize('viewAny', MailTypology::class);

        $page = MailTypology::query()->paginate(20)->withQueryString();

        return response()->json($this->paginatedResponse($page, MailTypologyResource::class));
    }

    /**
     * GET /api/v1/search/mails/feedback?type=true&deadline=available|exceeded
     */
    public function feedback(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Mail::class);

        $type = $request->input('type');
        $deadline = $request->input('deadline');

        $query = MailTransaction::query()
            ->whereHas('action', function (Builder $q) use ($type, $deadline) {
                if ($type === 'true' && $deadline === 'available') {
                    $q->where('to_return', true);
                } elseif ($type === 'true' && $deadline === 'exceeded') {
                    $q->whereRaw('DATE_ADD(mail_transactions.date_creation, INTERVAL mail_actions.duration SECOND) > NOW()');
                } else {
                    $q->where('to_return', false);
                }
            });

        $transactions = $query->with(['mail', 'action', 'organisationReceived', 'organisationSend'])->get();

        return response()->json(['data' => MailTransactionResource::collection($transactions)]);
    }

    private function applyDateCriteria(Builder $query, Request $request): void
    {
        if ($request->filled('date_exact')) {
            $query->whereDate('date', $request->input('date_exact'));
        } elseif ($request->filled('date_start') && $request->filled('date_end')) {
            $query->whereBetween('date', [$request->input('date_start'), $request->input('date_end')]);
        } elseif ($request->filled('date_start')) {
            $query->where('date', '>=', $request->input('date_start'));
        }
    }

    private function applyBatchCriteria(Builder $query, Request $request): void
    {
        $batchId = $request->input('batch_id', $request->input('id'));
        if ($batchId && Batch::find($batchId)) {
            $query->whereHas('batches', fn (Builder $q) => $q->where('batch_id', $batchId));
        }
    }

    private function applyContainerCriteria(Builder $query, Request $request): void
    {
        $containerId = $request->input('container_id');
        if ($containerId && MailContainer::find($containerId)) {
            $query->whereHas('containers', fn (Builder $q) => $q->where('mail_containers.id', $containerId));
        }
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
}
