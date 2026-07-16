@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
<div class="container-fluid py-3">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h1 class="h3 mb-1 text-primary">Tableau de bord</h1>
            <p class="text-muted mb-0">Bienvenue, {{ Auth::user()->name }} {{ Auth::user()->surname }}</p>
        </div>
        <span class="text-muted small">{{ now()->translatedFormat('l j F Y') }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ===== Bulles de notification du courrier ===== --}}
    @php
        // Bulles affichées selon le rôle : le DG cote et signe, le service valide
        // la réception, l'initiateur reprend ses courriers rejetés.
        $bubbles = [
            ['label' => 'Non lus',             'value' => $mailStats['unread'],     'color' => 'danger',  'icon' => 'bi-envelope',        'href' => route('mail-received.index'),  'show' => true],
            ['label' => 'À coter',             'value' => $mailStats['to_cote'],    'color' => 'primary', 'icon' => 'bi-diagram-3',       'href' => route('mails.incoming.index'), 'show' => $isDg],
            ['label' => 'À signer',            'value' => $mailStats['to_sign'],    'color' => 'warning', 'icon' => 'bi-pen',             'href' => route('mails.outgoing.index'), 'show' => $isDg],
            ['label' => 'Réception à valider', 'value' => $mailStats['to_confirm'], 'color' => 'success', 'icon' => 'bi-check2-circle',   'href' => route('mail-received.index'),  'show' => true],
            ['label' => 'À reprendre',         'value' => $mailStats['to_fix'],     'color' => 'secondary','icon' => 'bi-arrow-counterclockwise', 'href' => route('mail-send.index'), 'show' => true],
        ];
    @endphp

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0"><i class="bi bi-envelope-paper"></i> Mon courrier</h5>
                <a href="{{ route('mail-received.index') }}" class="small">Voir tous les courriers</a>
            </div>

            <div class="row g-3">
                @foreach($bubbles as $bubble)
                    @continue(!$bubble['show'])
                    <div class="col-6 col-md-4 col-lg">
                        <a href="{{ $bubble['href'] }}" class="text-decoration-none">
                            <div class="border rounded p-3 h-100 d-flex align-items-center gap-3 dashboard-bubble">
                                <span class="badge bg-{{ $bubble['color'] }} rounded-circle d-flex align-items-center justify-content-center"
                                      style="width:44px;height:44px;font-size:1rem;">
                                    {{ $bubble['value'] > 99 ? '99+' : $bubble['value'] }}
                                </span>
                                <span class="text-body">
                                    <i class="bi {{ $bubble['icon'] }} me-1 text-muted"></i>
                                    {{ $bubble['label'] }}
                                </span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- ===== Courriers en attente d'action ===== --}}
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-list-check"></i> Courriers en attente de votre action</h5>

                    <div class="list-group list-group-flush">
                        @forelse($priorityMails as $mail)
                            @php
                                // Étape du circuit à laquelle se trouve ce courrier.
                                $statusVal = $mail->status?->value;
                                if ($mail->mail_type === 'incoming' && !$mail->assigned_organisation_id) {
                                    $todo = ['À coter', 'bg-primary'];
                                    $link = route('mails.workflow.cote-form', $mail->id);
                                } elseif ($mail->mail_type === 'incoming') {
                                    $todo = ['Réception à valider', 'bg-success'];
                                    $link = route('mails.incoming.show', $mail->id);
                                } elseif ($statusVal === 'pending_approval') {
                                    $todo = ['À signer', 'bg-warning text-dark'];
                                    $link = route('mails.outgoing.show', $mail->id);
                                } else {
                                    $todo = ['Rejeté — à reprendre', 'bg-secondary'];
                                    $link = route('mails.outgoing.show', $mail->id);
                                }
                            @endphp
                            <a href="{{ $link }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 px-0">
                                <span class="text-truncate">
                                    <span class="d-block text-body">{{ $mail->name }}</span>
                                    <small class="text-muted">
                                        {{ $mail->code }}
                                        @if($mail->typology) — {{ $mail->typology->name }} @endif
                                        @if($mail->assignedOrganisation) — {{ $mail->assignedOrganisation->name }} @endif
                                    </small>
                                </span>
                                <span class="badge {{ $todo[1] }} flex-shrink-0">{{ $todo[0] }}</span>
                            </a>
                        @empty
                            <p class="text-center text-muted py-4 mb-0">Aucun courrier en attente de votre action.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Activité récente sur le courrier ===== --}}
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-clock-history"></i> Activité récente</h5>

                    <ul class="list-unstyled mb-0">
                        @forelse($recentActivities as $activity)
                            <li class="d-flex gap-2 pb-3 mb-3 border-bottom">
                                <i class="bi bi-dot fs-4 text-primary lh-1"></i>
                                <div class="small">
                                    @if($activity->user)<strong>{{ $activity->user->name }}</strong> — @endif
                                    {{ $activity->description ?? $activity->action }}
                                    @if($activity->mail)
                                        <a href="{{ route('mails.incoming.show', $activity->mail->id) }}">{{ $activity->mail->name }}</a>
                                    @endif
                                    <div class="text-muted">{{ $activity->created_at->diffForHumans() }}</div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center text-muted py-4">Aucune activité récente.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Actions rapides + volumétrie ===== --}}
    <div class="row g-4 mt-1">
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-lightning-charge"></i> Actions rapides</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('mail-send.create') }}" class="btn btn-outline-primary">
                            <i class="bi bi-envelope-plus"></i> Nouveau courrier
                        </a>
                        <a href="{{ route('mails.received.external.create') }}" class="btn btn-outline-primary">
                            <i class="bi bi-inbox"></i> Enregistrer un courrier reçu
                        </a>
                        <a href="{{ route('mails.advanced.form') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-search"></i> Recherche avancée
                        </a>
                        <a href="{{ route('folders.create') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-folder-plus"></i> Nouveau dossier
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-archive"></i> Répertoire numérique</h5>
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <a href="{{ route('folders.index') }}" class="text-decoration-none">
                                <div class="h4 mb-0 text-primary">{{ $stats['folders'] }}</div>
                                <small class="text-muted">Dossiers</small>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('documents.index') }}" class="text-decoration-none">
                                <div class="h4 mb-0 text-primary">{{ $stats['documents'] }}</div>
                                <small class="text-muted">Documents</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .dashboard-bubble {
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
    .dashboard-bubble:hover {
        border-color: var(--bs-primary) !important;
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075);
    }
</style>
@endpush
