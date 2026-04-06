@extends('layouts.app')

@push('styles')
<style>
    /* ── KPI cards ─────────────────────────────────────────── */
    .inst-kpi { display:flex; gap:16px; flex-wrap:wrap; margin-bottom:24px; }
    .inst-kpi-item {
        flex:1; min-width:130px;
        background:#fff; border-radius:14px;
        box-shadow:0 2px 10px rgba(0,0,0,.06);
        padding:16px 20px; display:flex; align-items:center; gap:14px;
    }
    .inst-kpi-icon { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0; }
    .ki-running  .inst-kpi-icon { background:#cfe2ff; color:#084298; }
    .ki-done     .inst-kpi-icon { background:#d1e7dd; color:#0a3622; }
    .ki-paused   .inst-kpi-icon { background:#fff3cd; color:#856404; }
    .ki-cancel   .inst-kpi-icon { background:#f8d7da; color:#842029; }
    .ki-num   { font-size:1.8rem; font-weight:700; line-height:1; }
    .ki-label { font-size:.75rem; color:#6c757d; text-transform:uppercase; letter-spacing:.05em; }

    /* ── Table ─────────────────────────────────────────────── */
    .inst-table { border-collapse:separate; border-spacing:0; }
    .inst-table thead th {
        background:#f8f9fa; font-size:.78rem; text-transform:uppercase;
        letter-spacing:.05em; color:#6c757d; font-weight:600;
        border-bottom:2px solid #e9ecef; padding:10px 14px; white-space:nowrap;
    }
    .inst-table tbody tr { border-bottom:1px solid #f1f3f5; transition:background .12s; }
    .inst-table tbody tr:hover { background:#fafbfc; }
    .inst-table tbody td { padding:12px 14px; vertical-align:middle; }

    /* ── Status badges ─────────────────────────────────────── */
    .s-badge { font-size:.72rem;font-weight:700;padding:4px 10px;border-radius:20px;display:inline-flex;align-items:center;gap:4px; }
    .sb-running   { background:#cfe2ff; color:#084298; }
    .sb-completed { background:#d1e7dd; color:#0a3622; }
    .sb-paused    { background:#fff3cd; color:#856404; }
    .sb-cancelled { background:#f8d7da; color:#842029; }

    /* ── Progress bar ──────────────────────────────────────── */
    .inst-progress { height:6px; border-radius:10px; background:#e9ecef; overflow:hidden; margin-top:4px; }
    .inst-progress-bar { height:100%; border-radius:10px; background:#0d6efd; transition:width .3s; }

    /* ── Action btn ────────────────────────────────────────── */
    .action-btn { width:30px;height:30px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;border:none;cursor:pointer;text-decoration:none;transition:opacity .15s; }
    .action-btn:hover { opacity:.78; }
    .ab-view  { background:#e0f0ff; color:#0553b1; }
    .ab-del   { background:#f8d7da; color:#842029; }

    /* ── Empty ─────────────────────────────────────────────── */
    .empty-state { padding:60px 20px; text-align:center; }
    .empty-icon  { font-size:4rem; color:#dee2e6; margin-bottom:16px; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Header ─────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-collection-play text-primary"></i>
                {{ __('Instances en cours') }}
            </h2>
            <p class="text-muted small mb-0">{{ __('Suivi des processus lancés') }}</p>
        </div>
        <a href="{{ route('workflows.instances.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-play-fill"></i> {{ __('Démarrer un processus') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── KPI Row ─────────────────────────────────────── --}}
    <div class="inst-kpi">
        <div class="inst-kpi-item ki-running">
            <div class="inst-kpi-icon"><i class="bi bi-arrow-repeat"></i></div>
            <div>
                <div class="ki-num">{{ $instances->where('status','running')->count() }}</div>
                <div class="ki-label">{{ __('En cours') }}</div>
            </div>
        </div>
        <div class="inst-kpi-item ki-done">
            <div class="inst-kpi-icon"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="ki-num">{{ $instances->where('status','completed')->count() }}</div>
                <div class="ki-label">{{ __('Terminés') }}</div>
            </div>
        </div>
        <div class="inst-kpi-item ki-paused">
            <div class="inst-kpi-icon"><i class="bi bi-pause-circle"></i></div>
            <div>
                <div class="ki-num">{{ $instances->where('status','paused')->count() }}</div>
                <div class="ki-label">{{ __('En pause') }}</div>
            </div>
        </div>
        <div class="inst-kpi-item ki-cancel">
            <div class="inst-kpi-icon"><i class="bi bi-x-circle"></i></div>
            <div>
                <div class="ki-num">{{ $instances->where('status','cancelled')->count() }}</div>
                <div class="ki-label">{{ __('Annulés') }}</div>
            </div>
        </div>
    </div>

    {{-- ── List ─────────────────────────────────────────── --}}
    <div class="card border-0 shadow-sm" style="border-radius:14px;overflow:hidden;">
        @if($instances->count() > 0)
            <div class="table-responsive">
                <table class="table inst-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Instance') }}</th>
                            <th>{{ __('Processus') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Progression') }}</th>
                            <th>{{ __('Démarré par') }}</th>
                            <th>{{ __('Démarré le') }}</th>
                            <th>{{ __('Fin') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instances as $instance)
                            @php
                                $total     = $instance->tasks->count();
                                $done      = $instance->tasks->where('status','completed')->count();
                                $pct       = $total > 0 ? round($done / $total * 100) : 0;
                            @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('workflows.instances.show', $instance) }}"
                                       class="text-decoration-none text-dark fw-semibold">
                                        {{ $instance->name }}
                                    </a>
                                </td>
                                <td class="text-muted small">{{ $instance->definition->name ?? 'N/A' }}</td>
                                <td>
                                    @switch($instance->status)
                                        @case('running')   <span class="s-badge sb-running"><i class="bi bi-arrow-repeat"></i>{{ __('En cours') }}</span> @break
                                        @case('completed') <span class="s-badge sb-completed"><i class="bi bi-check-circle"></i>{{ __('Terminé') }}</span> @break
                                        @case('paused')    <span class="s-badge sb-paused"><i class="bi bi-pause-circle"></i>{{ __('En pause') }}</span> @break
                                        @case('cancelled') <span class="s-badge sb-cancelled"><i class="bi bi-x-circle"></i>{{ __('Annulé') }}</span> @break
                                    @endswitch
                                </td>
                                <td style="min-width:120px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="inst-progress flex-grow-1">
                                            <div class="inst-progress-bar" style="width:{{ $pct }}%;"></div>
                                        </div>
                                        <span class="small text-muted">{{ $done }}/{{ $total }}</span>
                                    </div>
                                </td>
                                <td class="small">{{ $instance->starter->name ?? 'N/A' }}</td>
                                <td class="small text-muted">{{ $instance->started_at->format('d/m/Y') }}</td>
                                <td class="small text-muted">
                                    {{ $instance->completed_at ? $instance->completed_at->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('workflows.instances.show', $instance) }}" class="action-btn ab-view" title="{{ __('Voir') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($instance->status !== 'completed' && $instance->status !== 'cancelled')
                                            <form action="{{ route('workflows.instances.destroy', $instance) }}" method="POST"
                                                  onsubmit="return confirm('{{ __('Annuler cette instance ?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn ab-del" title="{{ __('Annuler') }}">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center py-3 border-top">
                {{ $instances->links() }}
            </div>

        @else
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-collection-play"></i></div>
                <h5 class="fw-semibold text-muted">{{ __('Aucune instance trouvée') }}</h5>
                <p class="text-muted small">{{ __('Lancez votre premier processus pour commencer le suivi.') }}</p>
                <a href="{{ route('workflows.instances.create') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-play-fill me-1"></i> {{ __('Démarrer un processus') }}
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
