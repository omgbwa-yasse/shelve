<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EmailAccountResource;
use App\Jobs\SyncEmailAccountJob;
use App\Models\EmailAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class EmailAccountController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'is_active'];
    private const SORTABLE = ['id', 'name', 'created_at'];

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', EmailAccount::class);

        $query = EmailAccount::byOrganisation(Auth::user()->current_organisation_id);

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'name');

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, EmailAccountResource::class));
    }

    public function show(EmailAccount $emailAccount): JsonResponse
    {
        $this->authorize('view', $emailAccount);

        return response()->json(['data' => new EmailAccountResource($emailAccount)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', EmailAccount::class);

        $data = $this->validateAccount($request);
        $data['created_by'] = Auth::id();
        $data['user_id'] = Auth::id();

        $account = EmailAccount::create($data);

        return response()->json(
            ['data' => new EmailAccountResource($account)],
            201,
            ['Location' => "/api/v1/email-accounts/{$account->id}"]
        );
    }

    public function update(Request $request, EmailAccount $emailAccount): JsonResponse
    {
        $this->authorize('update', $emailAccount);

        $data = $this->validateAccount($request, $emailAccount);
        $data['updated_by'] = Auth::id();

        $emailAccount->update($data);

        return response()->json(['data' => new EmailAccountResource($emailAccount->fresh())]);
    }

    public function destroy(EmailAccount $emailAccount): Response
    {
        $this->authorize('delete', $emailAccount);

        $emailAccount->delete();

        return response()->noContent();
    }

    public function sync(EmailAccount $emailAccount): JsonResponse
    {
        $this->authorize('update', $emailAccount);

        SyncEmailAccountJob::dispatch($emailAccount);

        return response()->json(['message' => 'Synchronisation lancée en arrière-plan.']);
    }

    public function toggleActive(EmailAccount $emailAccount): JsonResponse
    {
        $this->authorize('update', $emailAccount);

        $emailAccount->update(['is_active' => ! $emailAccount->is_active]);

        return response()->json(['data' => new EmailAccountResource($emailAccount->fresh())]);
    }

    /** GET /api/v1/email — utilisé par Next.js pour masquer la section Email du menu. */
    public function moduleStatus(): JsonResponse
    {
        $organisation = Auth::user()->currentOrganisation;

        return response()->json(['data' => ['enabled' => (bool) $organisation?->email_module_enabled]]);
    }

    public function toggleModule(): JsonResponse
    {
        abort_unless(Auth::user()->hasPermission('email_account_update'), 403);

        $organisation = Auth::user()->currentOrganisation;
        abort_unless($organisation, 404);

        $organisation->update(['email_module_enabled' => ! $organisation->email_module_enabled]);

        return response()->json(['data' => ['enabled' => (bool) $organisation->email_module_enabled]]);
    }

    private function validateAccount(Request $request, ?EmailAccount $account = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email_address' => 'required|email|max:255',
            'imap_host' => 'required|string|max:255',
            'imap_port' => 'required|integer|min:1|max:65535',
            'imap_encryption' => 'required|in:ssl,tls,none',
            'imap_username' => 'required|string|max:255',
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer|min:1|max:65535',
            'smtp_encryption' => 'required|in:ssl,tls,none',
            'smtp_username' => 'required|string|max:255',
            'default_from_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];

        $rules['imap_password'] = $account ? 'nullable|string' : 'required|string';
        $rules['smtp_password'] = $account ? 'nullable|string' : 'required|string';

        $data = $request->validate($rules);

        if (empty($data['imap_password'])) {
            unset($data['imap_password']);
        }
        if (empty($data['smtp_password'])) {
            unset($data['smtp_password']);
        }

        return $data;
    }
}
