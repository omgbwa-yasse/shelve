<?php

namespace App\Http\Controllers;

use App\Models\EmailAccount;
use App\Models\EmailAttachment;
use App\Models\EmailMessage;
use App\Models\EmailTag;
use App\Services\Email\EmailSendService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Boîte de messagerie (réception/envoi) — consomme le miroir local
 * `email_messages` alimenté par `EmailSyncService`. Les actions lecture/
 * favori restent locales pour la v1 (pas de ré-écriture des flags IMAP côté
 * serveur) ; l'envoi, lui, part réellement via SMTP (`EmailSendService`).
 */
class MailboxController extends Controller
{
    public function inbox(Request $request)
    {
        return $this->folder($request, 'INBOX', 'mails.email.inbox');
    }

    public function sent(Request $request)
    {
        return $this->folder($request, 'Sent', 'mails.email.sent');
    }

    private function folder(Request $request, string $folder, string $view)
    {
        Gate::authorize('viewAny', EmailMessage::class);

        $account = $this->resolveAccount($request);

        $messages = $account
            ? EmailMessage::where('email_account_id', $account->id)
                ->folder($folder)
                ->when($request->filled('q'), fn ($q) => $q->where('subject', 'like', '%'.$request->q.'%'))
                ->when($request->filled('tag'), fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('email_tags.id', $request->tag)))
                ->with('tags')
                ->orderByDesc('sent_at')
                ->paginate(25)
            : EmailMessage::whereRaw('1 = 0')->paginate(25);

        return view($view, [
            'messages' => $messages,
            'account' => $account,
            'accounts' => $this->accountsForUser(),
            'tags' => $account ? EmailTag::byOrganisation($account->organisation_id)->orderBy('name')->get() : collect(),
        ]);
    }

    public function show(EmailMessage $emailMessage)
    {
        Gate::authorize('view', $emailMessage);

        $emailMessage->load('attachments', 'tags', 'account');

        if (! $emailMessage->is_read) {
            $emailMessage->update(['is_read' => true]);
        }

        return view('mails.email.show', [
            'message' => $emailMessage,
            'tags' => EmailTag::byOrganisation($emailMessage->organisation_id)->orderBy('name')->get(),
        ]);
    }

    public function compose(Request $request)
    {
        Gate::authorize('create', EmailMessage::class);

        $account = $this->resolveAccount($request);
        $replyTo = $request->filled('reply_to') ? EmailMessage::find($request->reply_to) : null;

        return view('mails.email.compose', [
            'account' => $account,
            'accounts' => $this->accountsForUser(),
            'replyTo' => $replyTo,
        ]);
    }

    public function send(Request $request, EmailSendService $sendService)
    {
        Gate::authorize('create', EmailMessage::class);

        $validated = $request->validate([
            'email_account_id' => 'required|exists:email_accounts,id',
            'to' => 'required|string',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'in_reply_to' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $account = EmailAccount::findOrFail($validated['email_account_id']);
        Gate::authorize('view', $account);

        $sendService->send($account, [
            'to' => $this->splitAddresses($validated['to']),
            'cc' => $this->splitAddresses($validated['cc'] ?? ''),
            'bcc' => $this->splitAddresses($validated['bcc'] ?? ''),
            'subject' => $validated['subject'],
            'body_html' => $validated['body_html'],
            'in_reply_to' => $validated['in_reply_to'] ?? null,
            'attachments' => $request->file('attachments', []),
        ]);

        return redirect()->route('mails.email.sent')->with('success', 'Message envoyé.');
    }

    public function downloadAttachment(EmailMessage $emailMessage, EmailAttachment $emailAttachment): StreamedResponse
    {
        Gate::authorize('view', $emailMessage);

        abort_if($emailAttachment->email_message_id !== $emailMessage->id, 404);

        return Storage::disk($emailAttachment->disk)->download($emailAttachment->path, $emailAttachment->filename);
    }

    public function toggleFlag(EmailMessage $emailMessage)
    {
        Gate::authorize('update', $emailMessage);

        $emailMessage->update(['is_flagged' => ! $emailMessage->is_flagged]);

        return back();
    }

    public function destroy(EmailMessage $emailMessage)
    {
        Gate::authorize('delete', $emailMessage);

        $emailMessage->delete();

        return back()->with('success', 'Message supprimé du miroir local.');
    }

    public function attachTag(Request $request, EmailMessage $emailMessage)
    {
        Gate::authorize('update', $emailMessage);

        $request->validate(['tag_id' => 'required|exists:email_tags,id']);

        $emailMessage->tags()->syncWithoutDetaching([$request->tag_id]);

        return back();
    }

    public function detachTag(EmailMessage $emailMessage, EmailTag $emailTag)
    {
        Gate::authorize('update', $emailMessage);

        $emailMessage->tags()->detach($emailTag->id);

        return back();
    }

    private function resolveAccount(Request $request): ?EmailAccount
    {
        $accounts = $this->accountsForUser();

        if ($request->filled('account')) {
            return $accounts->firstWhere('id', (int) $request->account) ?? $accounts->first();
        }

        return $accounts->first();
    }

    private function accountsForUser()
    {
        return EmailAccount::byOrganisation(Auth::user()->current_organisation_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function splitAddresses(string $raw): array
    {
        return collect(explode(',', $raw))
            ->map(fn ($a) => trim($a))
            ->filter()
            ->values()
            ->all();
    }
}
