@extends('layouts.app')

@section('content')
@php
    $statusLabels = [
        'active' => ['En validation', 'primary'], 'blocked' => ['Bloqué : action requise', 'warning'],
        'revision_requested' => ['Correction demandée', 'warning'], 'rejected' => ['Rejeté', 'danger'],
        'completed' => ['Toutes les validations sont acquises', 'success'], 'cancelled' => ['Annulé', 'secondary'],
    ];
    $stepLabels = [
        'waiting' => ['En attente de l’étape précédente', 'secondary', 'bi-hourglass'],
        'pending' => ['À valider', 'primary', 'bi-hourglass-split'], 'expired' => ['Délai dépassé', 'danger', 'bi-alarm'],
        'approved' => ['Approuvé', 'success', 'bi-check-circle'], 'rejected' => ['Rejeté', 'danger', 'bi-x-circle'],
        'revision_requested' => ['Correction demandée', 'warning', 'bi-arrow-counterclockwise'],
        'abstained' => ['Abstention - arbitrage requis', 'warning', 'bi-dash-circle'],
        'skipped' => ['Non déclenchée par les conditions', 'light text-dark', 'bi-skip-forward'],
        'cancelled' => ['Annulée', 'secondary', 'bi-slash-circle'],
    ];
    [$circuitLabel, $circuitColor] = $statusLabels[$circuit->status] ?? [$circuit->status, 'secondary'];
    $currentUser = auth()->user();
    $canManage = $currentUser->isSuperAdmin() || (int) $circuit->initiated_by === (int) $currentUser->id || (int) $circuit->mail->sender_user_id === (int) $currentUser->id;
    $mailRoute = $circuit->mail->mail_type === 'incoming' ? route('mails.incoming.show', $circuit->mail) : ($circuit->mail->mail_type === 'outgoing' ? route('mails.outgoing.show', $circuit->mail) : route('mail-send.show', $circuit->mail));
@endphp
<div class="container-fluid py-3">
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-1"></i>{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase text-primary small fw-semibold">Circuit métier - version {{ $circuit->version }}</div>
            <h3 class="mb-1">{{ $circuit->template_name }}</h3>
            <div class="text-muted"><a href="{{ $mailRoute }}" class="text-decoration-none">{{ $circuit->mail->code }} - {{ $circuit->mail->name }}</a></div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @if($circuit->mail->access_restricted)<span class="badge bg-dark fs-6"><i class="bi bi-lock me-1"></i>Confidentiel</span>@endif
            <span class="badge bg-{{ $circuitColor }} fs-6">{{ $circuitLabel }}</span>
            <a href="{{ route('mails.circulations.index') }}" class="btn btn-outline-secondary"><i class="bi bi-list me-1"></i>Circulations</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card border-0 bg-light h-100"><div class="card-body"><div class="text-muted small">Initiateur</div><div class="fw-semibold">{{ $circuit->initiator ? trim($circuit->initiator->name.' '.($circuit->initiator->surname ?? '')) : '—' }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 bg-light h-100"><div class="card-body"><div class="text-muted small">Organisation du circuit</div><div class="fw-semibold">{{ ucfirst($circuit->mode) }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 bg-light h-100"><div class="card-body"><div class="text-muted small">Début</div><div class="fw-semibold">{{ optional($circuit->started_at)->format('d/m/Y H:i') }}</div></div></div></div>
        <div class="col-md-3"><div class="card border-0 bg-light h-100"><div class="card-body"><div class="text-muted small">Montant</div><div class="fw-semibold">{{ isset($circuit->metadata['amount']) && $circuit->metadata['amount'] !== null ? number_format($circuit->metadata['amount'], 0, ',', ' ').' FCFA' : 'Non renseigné' }}</div></div></div></div>
    </div>

    @if($circuit->metadata['note'] ?? null)<div class="alert alert-secondary"><strong>Note de circulation :</strong> {{ $circuit->metadata['note'] }}</div>@endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-signpost-split me-2"></i>Chaîne de validation</h5>
            <span class="text-muted small">Les cartes d’un même niveau sont exécutées en parallèle.</span>
        </div>
        <div class="card-body">
            @foreach($circuit->steps->groupBy('stage') as $stage => $steps)
                <div class="d-flex align-items-center gap-2 mb-2 {{ !$loop->first ? 'mt-4' : '' }}">
                    <span class="badge rounded-pill bg-dark">Étape {{ $stage }}</span>
                    @if($steps->count() > 1)<span class="badge bg-info text-dark"><i class="bi bi-arrows-angle-expand me-1"></i>{{ $steps->count() }} validations parallèles</span>@endif
                </div>
                <div class="row g-3">
                    @foreach($steps as $step)
                        @php
                            [$stepLabel, $stepColor, $stepIcon] = $stepLabels[$step->status] ?? [$step->status, 'secondary', 'bi-circle'];
                            $canAct = $currentUser->isSuperAdmin() || (int) $step->assigned_user_id === (int) $currentUser->id;
                            $canReassign = $canAct || $canManage;
                        @endphp
                        <div class="col-lg-{{ $steps->count() > 1 ? '6' : '12' }}">
                            <div class="card h-100 border-{{ str_contains($stepColor, ' ') ? 'light' : $stepColor }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between gap-2 mb-2">
                                        <div><div class="fw-semibold">{{ $step->label }}</div><div class="text-muted small">Fonction : {{ strtoupper($step->validator_value) }}</div></div>
                                        <span class="badge bg-{{ $stepColor }} align-self-start"><i class="bi {{ $stepIcon }} me-1"></i>{{ $stepLabel }}</span>
                                    </div>
                                    <div class="small mb-2"><i class="bi bi-person me-1"></i><strong>{{ $step->assignedUser ? trim($step->assignedUser->name.' '.($step->assignedUser->surname ?? '')) : 'Aucun valideur' }}</strong></div>
                                    @if($step->due_at)<div class="small {{ $step->status === 'expired' ? 'text-danger fw-semibold' : 'text-muted' }}"><i class="bi bi-clock me-1"></i>Échéance : {{ $step->due_at->format('d/m/Y H:i') }}</div>@endif
                                    @if($step->comment)<div class="alert alert-light border mt-3 mb-0 py-2"><i class="bi bi-chat-left-text me-1"></i>{{ $step->comment }}</div>@endif

                                    @if($canAct && $step->isActionable())
                                        <form action="{{ route('mails.circuit-steps.act', $step) }}" method="POST" class="border-top mt-3 pt-3">
                                            @csrf
                                            <label class="form-label small fw-semibold">Commentaire de décision</label>
                                            <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Obligatoire pour correction, rejet ou abstention"></textarea>
                                            <div class="d-flex flex-wrap gap-2">
                                                <button name="action" value="approve" class="btn btn-success btn-sm"><i class="bi bi-check2 me-1"></i>Approuver</button>
                                                <button name="action" value="approve_with_comment" class="btn btn-outline-success btn-sm"><i class="bi bi-chat-check me-1"></i>Approuver avec commentaire</button>
                                                <button name="action" value="revision" class="btn btn-warning btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Demander correction</button>
                                                <button name="action" value="reject" class="btn btn-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Rejeter</button>
                                                <button name="action" value="abstain" class="btn btn-outline-secondary btn-sm"><i class="bi bi-dash-circle me-1"></i>S’abstenir</button>
                                            </div>
                                        </form>
                                    @endif

                                    @if($canReassign && in_array($step->status, ['pending', 'expired', 'abstained']))
                                        <details class="mt-3">
                                            <summary class="small text-primary" style="cursor:pointer;"><i class="bi bi-person-gear me-1"></i>Déléguer ou escalader cette étape</summary>
                                            <div class="row g-2 mt-1">
                                                @foreach(['delegate' => ['Déléguer', 'btn-outline-primary'], 'escalate' => ['Escalader', 'btn-outline-danger']] as $operation => [$label, $class])
                                                    <div class="col-md-6"><form action="{{ route('mails.circuit-steps.'.$operation, $step) }}" method="POST" class="border rounded p-2 h-100">
                                                        @csrf
                                                        <select name="assignee_id" class="form-select form-select-sm mb-2" required><option value="">Nouveau décideur</option>@foreach($users as $user)<option value="{{ $user->id }}">{{ trim($user->name.' '.($user->surname ?? '')) }}</option>@endforeach</select>
                                                        <input name="reason" class="form-control form-control-sm mb-2" required placeholder="Motif obligatoire">
                                                        <button class="btn {{ $class }} btn-sm w-100">{{ $label }}</button>
                                                    </form></div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if(!$loop->last)<div class="text-center text-muted my-2"><i class="bi bi-arrow-down fs-4"></i></div>@endif
            @endforeach
        </div>
    </div>

    @if($canManage)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="bi bi-sliders me-2"></i>Actions de l’initiateur</h5></div>
            <div class="card-body d-flex flex-wrap gap-3 align-items-end">
                @if($circuit->status === 'revision_requested')
                    <form action="{{ route('mails.circuits.resume', $circuit) }}" method="POST" class="flex-grow-1">@csrf<label class="form-label">Résumé des corrections effectuées</label><div class="input-group"><input name="comment" class="form-control" placeholder="Nouvelle version et pièces corrigées"><button class="btn btn-primary"><i class="bi bi-arrow-repeat me-1"></i>Resoumettre toutes les validations</button></div></form>
                @endif
                @if($circuit->status === 'completed' && $circuit->mail->status?->value !== 'transmitted')
                    <form action="{{ route('mails.circuits.transmit', $circuit) }}" method="POST">@csrf<input type="hidden" name="comment" value="Transmission après validation complète"><button class="btn btn-success btn-lg"><i class="bi bi-send-check me-1"></i>Transmettre le courrier validé</button></form>
                @endif
                @if(in_array($circuit->status, ['active', 'blocked', 'revision_requested']))
                    <form action="{{ route('mails.circuits.cancel', $circuit) }}" method="POST" class="flex-grow-1">@csrf<label class="form-label">Annuler et choisir un autre circuit</label><div class="input-group"><input name="reason" class="form-control" required placeholder="Motif de l’annulation"><button class="btn btn-outline-danger"><i class="bi bi-x-octagon me-1"></i>Annuler le circuit</button></div></form>
                @endif
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historique complet et traçable</h5></div>
        <div class="list-group list-group-flush">
            @forelse($circuit->actions as $action)
                <div class="list-group-item py-3">
                    <div class="d-flex justify-content-between gap-3"><div><span class="badge bg-light text-dark border me-2">v{{ $action->version }}</span><strong>{{ str_replace('_', ' ', ucfirst($action->action)) }}</strong>@if($action->step)<span class="text-muted"> - {{ $action->step->label }}</span>@endif</div><span class="text-muted small">{{ optional($action->created_at)->format('d/m/Y H:i') }}</span></div>
                    <div class="small text-muted mt-1">{{ $action->actor ? trim($action->actor->name.' '.($action->actor->surname ?? '')) : 'Système' }}@if($action->comment) - {{ $action->comment }}@endif</div>
                </div>
            @empty<div class="list-group-item text-muted">Aucun événement.</div>@endforelse
        </div>
    </div>
</div>
@endsection
