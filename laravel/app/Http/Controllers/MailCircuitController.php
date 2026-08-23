<?php

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Models\MailCircuit;
use App\Models\MailCircuitStep;
use App\Models\User;
use App\Services\Mail\MailCircuitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MailCircuitController extends Controller
{
    public function __construct(private readonly MailCircuitService $circuits)
    {
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $view = $request->string('view', 'mine')->toString();

        $query = MailCircuit::query()
            ->with(['mail.senderOrganisation', 'mail.recipientOrganisation', 'initiator', 'steps.assignedUser'])
            ->latest('id');

        if (! $user->isSuperAdmin() || $view === 'mine') {
            $query->where(function ($q) use ($user) {
                $q->where('initiated_by', $user->id)
                    ->orWhereHas('steps', fn ($steps) => $steps->where('assigned_user_id', $user->id))
                    ->orWhereHas('mail', function ($mails) use ($user) {
                        $mails->where('sender_user_id', $user->id)
                            ->orWhere('recipient_user_id', $user->id)
                            ->orWhere('assigned_to', $user->id)
                            ->orWhere('sender_organisation_id', $user->current_organisation_id)
                            ->orWhere('recipient_organisation_id', $user->current_organisation_id)
                            ->orWhere('assigned_organisation_id', $user->current_organisation_id)
                            ->orWhereHas('cotations', fn ($cotations) => $cotations->where('organisation_id', $user->current_organisation_id));
                    });
            });
        }

        if ($view === 'pending') {
            $query->whereIn('status', [MailCircuit::STATUS_ACTIVE, MailCircuit::STATUS_BLOCKED, MailCircuit::STATUS_REVISION]);
        }

        $items = $query->paginate(20)->withQueryString();
        foreach ($items as $circuit) {
            if (in_array($circuit->status, [MailCircuit::STATUS_ACTIVE, MailCircuit::STATUS_BLOCKED], true)) {
                $this->circuits->refreshExpired($circuit);
            }
        }

        return view('mails.circuits.index', compact('items', 'view'));
    }

    public function create(Mail $mail)
    {
        $this->authorize('update', $mail);

        return view('mails.circuits.create', [
            'mail' => $mail,
            'templates' => $this->circuits->templates(),
            'users' => $this->availableUsers(),
            'threshold' => config('mail_circuits.financial_threshold', 5000000),
        ]);
    }

    public function store(Request $request, Mail $mail)
    {
        $this->authorize('update', $mail);
        $templateCodes = array_keys($this->circuits->templates());

        $data = $request->validate([
            'template_code' => ['required', Rule::in($templateCodes)],
            'note' => 'nullable|string|max:2000',
            'amount' => 'nullable|numeric|min:0',
            'finance_required' => 'nullable|boolean',
            'legal_required' => 'nullable|boolean',
            'dsi_required' => 'nullable|boolean',
            'dg_required' => 'nullable|boolean',
            'official_response' => 'nullable|boolean',
            'sensitive' => 'nullable|boolean',
            'urgent' => 'nullable|boolean',
            'validators' => 'nullable|array',
            'validators.*' => 'nullable|integer|exists:users,id',
            'custom_mode' => 'nullable|in:sequential,parallel',
            'custom_validators' => 'nullable|array|min:1|max:4',
            'custom_validators.*' => 'nullable|integer|exists:users,id',
            'custom_labels' => 'nullable|array|max:4',
            'custom_labels.*' => 'nullable|string|max:120',
        ]);

        $circuit = $this->circuits->start($mail, $data['template_code'], Auth::user(), $data);

        return redirect()->route('mails.circuits.show', $circuit)
            ->with('success', 'Le circuit a été démarré. Les étapes actives sont maintenant visibles.');
    }

    public function show(MailCircuit $circuit)
    {
        $this->ensureCanView($circuit);
        $circuit = $this->circuits->refreshExpired($circuit);

        return view('mails.circuits.show', [
            'circuit' => $circuit->load(['mail', 'initiator', 'steps.assignedUser', 'steps.actor', 'actions.actor', 'actions.step']),
            'users' => $this->availableUsers(),
        ]);
    }

    public function act(Request $request, MailCircuitStep $step)
    {
        $step->loadMissing('circuit.mail');
        $this->ensureCanAct($step);

        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'approve_with_comment', 'revision', 'reject', 'abstain'])],
            'comment' => 'nullable|string|max:2000',
        ]);

        $this->circuits->act($step, $data['action'], Auth::user(), $data['comment'] ?? null);

        return back()->with('success', 'La décision a été enregistrée et le circuit a été recalculé.');
    }

    public function delegate(Request $request, MailCircuitStep $step)
    {
        $step->loadMissing('circuit.mail');
        $this->ensureCanManageStep($step);
        $data = $request->validate([
            'assignee_id' => 'required|integer|exists:users,id',
            'reason' => 'required|string|max:1000',
        ]);

        $this->circuits->delegate($step, User::findOrFail($data['assignee_id']), Auth::user(), $data['reason']);

        return back()->with('success', 'La validation a été déléguée et l’historique a été conservé.');
    }

    public function escalate(Request $request, MailCircuitStep $step)
    {
        $step->loadMissing('circuit.mail');
        $this->ensureCanManageStep($step);
        $data = $request->validate([
            'assignee_id' => 'required|integer|exists:users,id',
            'reason' => 'required|string|max:1000',
        ]);

        $this->circuits->escalate($step, User::findOrFail($data['assignee_id']), Auth::user(), $data['reason']);

        return back()->with('success', 'L’étape a été escaladée vers le nouveau décideur.');
    }

    public function resume(Request $request, MailCircuit $circuit)
    {
        $this->ensureCanManageCircuit($circuit);
        $data = $request->validate(['comment' => 'nullable|string|max:2000']);
        $this->circuits->resumeAfterRevision($circuit, Auth::user(), $data['comment'] ?? null);

        return back()->with('success', 'La nouvelle version a été resoumise. Toutes les validations ont été réinitialisées.');
    }

    public function cancel(Request $request, MailCircuit $circuit)
    {
        $this->ensureCanManageCircuit($circuit);
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        $this->circuits->cancel($circuit, Auth::user(), $data['reason']);

        return back()->with('success', 'Le circuit a été annulé. Un nouveau modèle peut maintenant être choisi.');
    }

    public function transmit(Request $request, MailCircuit $circuit)
    {
        $this->ensureCanManageCircuit($circuit);
        $data = $request->validate(['comment' => 'nullable|string|max:1000']);
        $this->circuits->transmit($circuit, Auth::user(), $data['comment'] ?? null);

        return back()->with('success', 'Le courrier validé a été marqué comme transmis.');
    }

    private function availableUsers()
    {
        return User::query()->with('currentOrganisation')->orderBy('name')->orderBy('surname')->get();
    }

    private function ensureCanView(MailCircuit $circuit): void
    {
        $user = Auth::user();
        $allowed = $user->isSuperAdmin()
            || (int) $circuit->initiated_by === (int) $user->id
            || $circuit->steps()->where('assigned_user_id', $user->id)->exists()
            || ($circuit->mail->access_restricted && $circuit->mail->canUserAccessRestricted($user))
            || $circuit->mail->involvesOrganisation((int) $user->current_organisation_id);

        abort_unless($allowed, 403, 'Vous ne participez pas à ce circuit.');
    }

    private function ensureCanAct(MailCircuitStep $step): void
    {
        $user = Auth::user();
        abort_unless(
            $user->isSuperAdmin() || (int) $step->assigned_user_id === (int) $user->id,
            403,
            'Cette décision appartient au valideur actuellement affecté.'
        );
    }

    private function ensureCanManageStep(MailCircuitStep $step): void
    {
        $user = Auth::user();
        abort_unless(
            $user->isSuperAdmin()
                || (int) $step->assigned_user_id === (int) $user->id
                || (int) $step->circuit->initiated_by === (int) $user->id
                || (int) $step->circuit->mail->sender_user_id === (int) $user->id,
            403,
            'Vous ne pouvez pas réaffecter cette étape.'
        );
    }

    private function ensureCanManageCircuit(MailCircuit $circuit): void
    {
        $user = Auth::user();
        abort_unless(
            $user->isSuperAdmin()
                || (int) $circuit->initiated_by === (int) $user->id
                || (int) $circuit->mail->sender_user_id === (int) $user->id,
            403,
            'Seul l’initiateur du circuit peut effectuer cette action.'
        );
    }
}
