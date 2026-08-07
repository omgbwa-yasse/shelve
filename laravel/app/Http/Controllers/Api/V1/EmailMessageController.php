<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EmailMessageResource;
use App\Models\EmailAccount;
use App\Models\EmailMessage;
use App\Services\Email\EmailSendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class EmailMessageController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'email_account_id', 'folder', 'is_read', 'is_flagged'];
    private const SORTABLE = ['id', 'sent_at', 'subject'];
    private const INCLUDABLE = ['tags', 'attachments'];

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', EmailMessage::class);

        $accountIds = EmailAccount::byOrganisation(Auth::user()->current_organisation_id)->pluck('id');

        $query = EmailMessage::whereIn('email_account_id', $accountIds);

        if ($request->filled('q')) {
            $query->where('subject', 'like', '%'.$request->string('q').'%');
        }

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'sent_at');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, EmailMessageResource::class));
    }

    public function show(Request $request, EmailMessage $emailMessage): JsonResponse
    {
        $this->authorize('view', $emailMessage);

        $emailMessage->load('tags', 'attachments');

        if (! $emailMessage->is_read) {
            $emailMessage->update(['is_read' => true]);
        }

        return response()->json(['data' => new EmailMessageResource($emailMessage)]);
    }

    public function update(Request $request, EmailMessage $emailMessage): JsonResponse
    {
        $this->authorize('update', $emailMessage);

        $data = $request->validate([
            'is_read' => 'sometimes|boolean',
            'is_flagged' => 'sometimes|boolean',
        ]);

        $emailMessage->update($data);

        return response()->json(['data' => new EmailMessageResource($emailMessage->fresh(['tags', 'attachments']))]);
    }

    public function destroy(EmailMessage $emailMessage): Response
    {
        $this->authorize('delete', $emailMessage);

        $emailMessage->delete();

        return response()->noContent();
    }

    /** POST /api/v1/email-messages/send — compose + envoi réel via SMTP (EmailSendService). */
    public function send(Request $request, EmailSendService $sendService): JsonResponse
    {
        $this->authorize('create', EmailMessage::class);

        $validated = $request->validate([
            'email_account_id' => 'required|exists:email_accounts,id',
            'to' => 'required|array|min:1',
            'to.*' => 'email',
            'cc' => 'nullable|array',
            'cc.*' => 'email',
            'bcc' => 'nullable|array',
            'bcc.*' => 'email',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'in_reply_to' => 'nullable|string',
        ]);

        $account = EmailAccount::findOrFail($validated['email_account_id']);
        $this->authorize('view', $account);

        $message = $sendService->send($account, [
            'to' => $validated['to'],
            'cc' => $validated['cc'] ?? [],
            'bcc' => $validated['bcc'] ?? [],
            'subject' => $validated['subject'],
            'body_html' => $validated['body_html'],
            'in_reply_to' => $validated['in_reply_to'] ?? null,
            'attachments' => $request->file('attachments', []),
        ]);

        return response()->json(['data' => new EmailMessageResource($message)], 201);
    }

    public function attachTag(Request $request, EmailMessage $emailMessage): JsonResponse
    {
        $this->authorize('update', $emailMessage);

        $request->validate(['tag_id' => 'required|exists:email_tags,id']);

        $emailMessage->tags()->syncWithoutDetaching([$request->tag_id]);

        return response()->json(['data' => new EmailMessageResource($emailMessage->fresh('tags'))]);
    }

    public function detachTag(EmailMessage $emailMessage, int $tagId): JsonResponse
    {
        $this->authorize('update', $emailMessage);

        $emailMessage->tags()->detach($tagId);

        return response()->json(['data' => new EmailMessageResource($emailMessage->fresh('tags'))]);
    }
}
