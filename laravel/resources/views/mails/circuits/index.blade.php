@extends('layouts.app')

@section('content')
@php
    $statusLabels = [
        'active' => ['En validation', 'primary'], 'blocked' => ['À relancer / escalader', 'warning'],
        'revision_requested' => ['Correction demandée', 'warning'], 'rejected' => ['Rejeté', 'danger'],
        'completed' => ['Validé', 'success'], 'cancelled' => ['Annulé', 'secondary'],
    ];
@endphp
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="text-uppercase text-primary small fw-semibold">Gestion du courrier</div>
            <h3 class="mb-1"><i class="bi bi-diagram-3 me-2"></i>Circulations et validations</h3>
            <p class="text-muted mb-0">Suivi des circuits hiérarchiques, spécialisés, parallèles et conditionnels.</p>
        </div>
        <a href="{{ route('mails.outgoing.index') }}" class="btn btn-outline-primary"><i class="bi bi-envelope me-1"></i>Choisir un courrier à faire circuler</a>
    </div>

    <div class="btn-group mb-3" role="group">
        <a href="{{ route('mails.circulations.index', ['view' => 'mine']) }}" class="btn {{ $view === 'mine' ? 'btn-primary' : 'btn-outline-primary' }}">Mes circuits</a>
        <a href="{{ route('mails.circulations.index', ['view' => 'pending']) }}" class="btn {{ $view === 'pending' ? 'btn-primary' : 'btn-outline-primary' }}">À traiter</a>
        @if(auth()->user()->isSuperAdmin())<a href="{{ route('mails.circulations.index', ['view' => 'all']) }}" class="btn {{ $view === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">Tous</a>@endif
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Courrier</th><th>Modèle</th><th>Progression</th><th>Étape en attente</th><th>Statut</th><th class="text-end">Action</th></tr></thead>
                <tbody>
                @forelse($items as $circuit)
                    @php
                        $steps = $circuit->steps;
                        $done = $steps->whereIn('status', ['approved', 'skipped'])->count();
                        $total = max(1, $steps->count());
                        $percent = (int) round(($done / $total) * 100);
                        [$label, $color] = $statusLabels[$circuit->status] ?? [$circuit->status, 'secondary'];
                        $pending = $steps->first(fn($step) => in_array($step->status, ['pending', 'expired'], true));
                    @endphp
                    <tr>
                        <td><div class="fw-semibold">{{ $circuit->mail->code }}</div><div class="text-muted small">{{ $circuit->mail->name }}</div></td>
                        <td><div>{{ $circuit->template_name }}</div><div class="text-muted small">Version {{ $circuit->version }} - {{ $circuit->mode }}</div></td>
                        <td style="min-width: 150px;"><div class="progress" style="height: 8px;"><div class="progress-bar" style="width: {{ $percent }}%"></div></div><div class="small text-muted mt-1">{{ $done }}/{{ $steps->count() }} étape(s)</div></td>
                        <td>
                            @if($pending)<div class="fw-semibold">{{ $pending->label }}</div><div class="small {{ $pending->status === 'expired' ? 'text-danger' : 'text-muted' }}">{{ $pending->assignedUser ? trim($pending->assignedUser->name.' '.($pending->assignedUser->surname ?? '')) : 'Non affecté' }}</div>@else<span class="text-muted">Aucune</span>@endif
                        </td>
                        <td><span class="badge bg-{{ $color }}">{{ $label }}</span></td>
                        <td class="text-end"><a href="{{ route('mails.circuits.show', $circuit) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>Ouvrir</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-diagram-3 fs-1 d-block mb-2"></i>Aucun circuit dans cette vue.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())<div class="card-footer bg-white">{{ $items->links() }}</div>@endif
    </div>
</div>
@endsection
