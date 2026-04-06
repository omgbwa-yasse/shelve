@extends('layouts.app')

@push('styles')
<style>
    /* ── Section cards ─────────────────────────────────────── */
    .section-card { background:#fff;border:1px solid #e9ecef;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden; }
    .section-card-header { padding:14px 20px;border-bottom:1px solid #f0f2f5;display:flex;align-items:center;gap:10px; }
    .section-card-header h5 { margin:0;font-size:.93rem;font-weight:700; }
    .section-card-body { padding:20px; }

    /* ── Status badge ─────────────────────────────────────── */
    .s-badge { font-size:.78rem;font-weight:700;padding:5px 12px;border-radius:20px;display:inline-flex;align-items:center;gap:5px; }
    .sb-running   { background:#cfe2ff;color:#084298; }
    .sb-completed { background:#d1e7dd;color:#0a3622; }
    .sb-paused    { background:#fff3cd;color:#856404; }
    .sb-cancelled { background:#f8d7da;color:#842029; }

    /* ── Task timeline ─────────────────────────────────────── */
    .task-timeline { list-style:none;padding:0;margin:0; }
    .tl-item { display:flex;align-items:flex-start;gap:0;position:relative; margin-bottom:0; }
    .tl-item:not(:last-child)::after {
        content:''; position:absolute; left:19px; top:44px; bottom:-10px;
        width:2px; background:#e9ecef; z-index:0;
    }
    .tl-num {
        width:40px;height:40px;min-width:40px;border-radius:50%;
        font-weight:700;font-size:.85rem;
        display:flex;align-items:center;justify-content:center;
        margin-right:14px;margin-top:2px;position:relative;z-index:1;flex-shrink:0;
    }
    .tl-num.s-done    { background:#d1e7dd;color:#0a3622; }
    .tl-num.s-active  { background:#0d6efd;color:#fff; }
    .tl-num.s-pending { background:#e9ecef;color:#6c757d; }
    .tl-card {
        flex:1; background:#fff; border:1px solid #e9ecef;
        border-radius:10px; padding:12px 16px; margin-bottom:10px;
        box-shadow:0 1px 5px rgba(0,0,0,.04);
    }
    .tl-card.tc-done   { border-left:3px solid #198754; }
    .tl-card.tc-active { border-left:3px solid #0d6efd; box-shadow:0 2px 12px rgba(13,110,253,.1); }
    .tl-title { font-weight:600; color:#1e293b; font-size:.9rem; }
    .tl-meta  { font-size:.75rem; color:#6c757d; margin-top:4px; }

    /* ── Task status pills ─────────────────────────────────── */
    .ts-pill { font-size:.7rem;font-weight:700;padding:3px 8px;border-radius:20px; }
    .ts-pending    { background:#f0f0f4;color:#555; }
    .ts-inprogress { background:#cfe2ff;color:#084298; }
    .ts-completed  { background:#d1e7dd;color:#0a3622; }
    .ts-cancelled  { background:#f8d7da;color:#842029; }

    /* ── Progress ring (overall) ───────────────────────────── */
    .progress-ring { position:relative; width:70px; height:70px; flex-shrink:0; }
    .progress-ring svg { transform:rotate(-90deg); }
    .ring-track { fill:none;stroke:#e9ecef;stroke-width:6; }
    .ring-fill  { fill:none;stroke:#0d6efd;stroke-width:6;stroke-linecap:round;transition:stroke-dashoffset .4s; }
    .ring-label { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;color:#1e293b; }

    /* ── Actions buttons ──────────────────────────────────── */
    .act-btn {
        display:flex;align-items:center;justify-content:center;gap:8px;
        width:100%;padding:10px;border-radius:10px;font-weight:600;font-size:.88rem;
        border:none;cursor:pointer;text-decoration:none;transition:opacity .15s;
    }
    .act-btn:hover { opacity:.85; }
    .btn-pause    { background:#fff3cd;color:#856404; }
    .btn-resume   { background:#cfe2ff;color:#084298; }
    .btn-complete { background:#d1e7dd;color:#0a3622; }
    .btn-cancel   { background:#f8d7da;color:#842029; }

    /* ── Marker start/end ─────────────────────────────────── */
    .tl-start,.tl-end {
        display:flex;align-items:center;gap:10px;padding:8px 14px;border-radius:8px;
        font-size:.85rem;font-weight:600;margin-bottom:8px;
    }
    .tl-start { background:#e0f0ff;color:#0553b1;border:1.5px dashed #84b9f4; }
    .tl-end   { background:#d1e7dd;color:#0a3622;border:1.5px dashed #86c9a3;margin-top:8px; }

    /* ── Note modal ───────────────────────────────────────── */
    .act-modal .modal-header { background:linear-gradient(135deg,#856404,#6d5004);color:#fff; }
    .act-modal .modal-content { border-radius:14px;overflow:hidden; }
    .act-modal .btn-close { filter:invert(1); }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Header ─────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted small mb-1 d-flex align-items-center gap-1">
                <a href="{{ route('workflows.instances.index') }}" class="text-decoration-none text-muted">
                    <i class="bi bi-collection-play"></i> {{ __('Instances') }}
                </a>
                <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
                {{ \Illuminate\Support\Str::limit($instance->name, 40) }}
            </p>
            <h2 class="fw-bold mb-0 d-flex align-items-center gap-2">
                {{ $instance->name }}
                @switch($instance->status)
                    @case('running')   <span class="s-badge sb-running"><i class="bi bi-arrow-repeat"></i>{{ __('En cours') }}</span> @break
                    @case('completed') <span class="s-badge sb-completed"><i class="bi bi-check-circle"></i>{{ __('Terminé') }}</span> @break
                    @case('paused')    <span class="s-badge sb-paused"><i class="bi bi-pause-circle"></i>{{ __('En pause') }}</span> @break
                    @case('cancelled') <span class="s-badge sb-cancelled"><i class="bi bi-x-circle"></i>{{ __('Annulé') }}</span> @break
                @endswitch
            </h2>
            @if($instance->status === 'paused' && isset($instance->current_state['pause_reason']))
                <div class="alert alert-warning py-1 px-2 mt-2 border-0 shadow-sm d-inline-flex align-items-center gap-2" style="font-size:.82rem;border-radius:8px;">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>{{ __('Raison de la pause') }} :</strong> {{ $instance->current_state['pause_reason'] }}
                </div>
            @endif
            <p class="text-muted small mb-0 mt-1">
                {{ __('Processus') }} :
                <a href="{{ route('workflows.definitions.show', $instance->definition) }}" class="text-decoration-none">
                    {{ $instance->definition->name }} (v{{ $instance->definition->version }})
                </a>
            </p>
        </div>
        <a href="{{ route('workflows.instances.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $total  = $instance->tasks->count();
        $done   = $instance->tasks->where('status','completed')->count();
        $pct    = $total > 0 ? round($done / $total * 100) : 0;
        $circ   = 2 * M_PI * 27; // radius 27
        $offset = $circ - ($pct / 100 * $circ);
    @endphp

    <div class="row g-4">

        {{-- ── Left: Task timeline ────────────────────── --}}
        <div class="col-lg-8">
            <div class="section-card">
                <div class="section-card-header">
                    <i class="bi bi-list-ol text-primary"></i>
                    <h5>{{ __('Avancement des étapes') }}</h5>
                    <div class="ms-auto d-flex align-items-center gap-3">
                        <span class="text-muted small">{{ $done }}/{{ $total }} {{ __('terminées') }}</span>
                        {{-- Progress ring --}}
                        <div class="progress-ring">
                            <svg width="70" height="70" viewBox="0 0 60 60">
                                <circle class="ring-track" cx="30" cy="30" r="27"/>
                                <circle class="ring-fill" cx="30" cy="30" r="27"
                                        stroke-dasharray="{{ $circ }}"
                                        stroke-dashoffset="{{ $offset }}"/>
                            </svg>
                            <div class="ring-label">{{ $pct }}%</div>
                        </div>
                    </div>
                </div>
                <div class="section-card-body">
                    @if($instance->tasks->count() > 0)
                        <div class="tl-start mb-2">
                            <i class="bi bi-play-circle-fill fs-5"></i> {{ __('Début du processus') }}
                        </div>
                        <ul class="task-timeline">
                            @foreach($instance->tasks->sortBy('sequence_order') as $task)
                                @php
                                    $numClass = match($task->status) {
                                        'completed'  => 's-done',
                                        'in_progress'=> 's-active',
                                        default      => 's-pending',
                                    };
                                    $cardClass = match($task->status) {
                                        'completed'  => 'tc-done',
                                        'in_progress'=> 'tc-active',
                                        default      => '',
                                    };
                                @endphp
                                <li class="tl-item">
                                    <div class="tl-num {{ $numClass }}">
                                        @if($task->status === 'completed')
                                            <i class="bi bi-check-lg"></i>
                                        @else
                                            {{ $task->sequence_order ?? $loop->index + 1 }}
                                        @endif
                                    </div>
                                    <div class="tl-card {{ $cardClass }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="tl-title">{{ $task->title }}</div>
                                                <div class="tl-meta d-flex align-items-center gap-2 mt-1">
                                                    @if($task->assignedUser)
                                                        <span><i class="bi bi-person me-1"></i>{{ $task->assignedUser->name }}</span>
                                                    @else
                                                        <span class="text-muted fst-italic">{{ __('Non assignée') }}</span>
                                                    @endif
                                                    @if($task->due_date)
                                                        <span class="{{ $task->isOverdue ? 'text-danger fw-semibold' : '' }}">
                                                            <i class="bi bi-calendar3 me-1"></i>{{ $task->due_date->format('d/m/Y') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                @switch($task->status)
                                                    @case('pending')     <span class="ts-pill ts-pending">{{ __('En attente') }}</span> @break
                                                    @case('in_progress') <span class="ts-pill ts-inprogress">{{ __('En cours') }}</span> @break
                                                    @case('completed')   <span class="ts-pill ts-completed">{{ __('Terminée') }}</span> @break
                                                    @case('cancelled')   <span class="ts-pill ts-cancelled">{{ __('Annulée') }}</span> @break
                                                @endswitch
                                                <a href="{{ route('tasks.show', $task) }}"
                                                   class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.75rem;">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="tl-end">
                            <i class="bi bi-check-circle-fill fs-5"></i> {{ __('Fin du processus') }}
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-list-task fs-1 d-block mb-2" style="opacity:.3;"></i>
                            {{ __('Aucune tâche n\'a été créée pour cette instance.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Right: Actions + Info ───────────────────── --}}
        <div class="col-lg-4">

            {{-- Actions --}}
            @if(in_array($instance->status, ['running','paused']))
                <div class="section-card mb-3">
                    <div class="section-card-header">
                        <i class="bi bi-lightning-charge text-warning"></i>
                        <h5>{{ __('Actions') }}</h5>
                    </div>
                    <div class="section-card-body d-flex flex-column gap-2">
                        @if($instance->status === 'running')
                            {{-- Pause with note --}}
                            <button type="button" class="act-btn btn-pause"
                                    data-bs-toggle="modal" data-bs-target="#pauseNoteModal">
                                <i class="bi bi-pause-circle-fill fs-5"></i>
                                {{ __('Mettre en pause') }}
                            </button>
                            {{-- Complete --}}
                            <form action="{{ route('workflows.instances.start', $instance) }}" method="POST"
                                  onsubmit="return confirm('{{ __('Marquer ce processus comme terminé ?') }}')">
                                @csrf
                                <button type="submit" class="act-btn btn-complete w-100">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                    {{ __('Terminer le processus') }}
                                </button>
                            </form>
                        @endif
                        @if($instance->status === 'paused')
                            <form action="{{ route('workflows.instances.resume', $instance) }}" method="POST">
                                @csrf
                                <button type="submit" class="act-btn btn-resume w-100">
                                    <i class="bi bi-play-circle-fill fs-5"></i>
                                    {{ __('Reprendre') }}
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('workflows.instances.cancel', $instance) }}" method="POST"
                              onsubmit="return confirm('{{ __('Annuler ce processus ? Cette action est irréversible.') }}')">
                            @csrf
                            <button type="submit" class="act-btn btn-cancel w-100">
                                <i class="bi bi-x-circle-fill fs-5"></i>
                                {{ __('Annuler le processus') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Stats --}}
            <div class="section-card mb-3">
                <div class="section-card-header">
                    <i class="bi bi-bar-chart-line text-info"></i>
                    <h5>{{ __('Statistiques') }}</h5>
                </div>
                <div class="section-card-body">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="p-2 rounded-3" style="background:#f8f9fa;">
                                <div style="font-size:1.5rem;font-weight:700;color:#6c757d;">{{ $total }}</div>
                                <div class="text-muted small">{{ __('Total') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3" style="background:#f8f9fa;">
                                <div style="font-size:1.5rem;font-weight:700;color:#0d6efd;">
                                    {{ $instance->tasks->where('status','in_progress')->count() }}
                                </div>
                                <div class="text-muted small">{{ __('En cours') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3" style="background:#f8f9fa;">
                                <div style="font-size:1.5rem;font-weight:700;color:#198754;">{{ $done }}</div>
                                <div class="text-muted small">{{ __('Terminées') }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded-3" style="background:#f8f9fa;">
                                <div style="font-size:1.5rem;font-weight:700;color:#856404;">
                                    {{ $instance->tasks->where('status','pending')->count() }}
                                </div>
                                <div class="text-muted small">{{ __('En attente') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info --}}
            <div class="section-card">
                <div class="section-card-header">
                    <i class="bi bi-info-circle text-secondary"></i>
                    <h5>{{ __('Informations') }}</h5>
                </div>
                <div class="section-card-body">
                    <dl class="row g-1 small mb-0">
                        <dt class="col-5 text-muted">{{ __('Démarré par') }}</dt>
                        <dd class="col-7 fw-semibold">{{ $instance->starter->name ?? 'N/A' }}</dd>

                        <dt class="col-5 text-muted">{{ __('Démarré le') }}</dt>
                        <dd class="col-7">{{ $instance->started_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-5 text-muted">{{ __('Durée') }}</dt>
                        <dd class="col-7">{{ $instance->started_at->diffForHumans(null, true) }}</dd>

                        @if($instance->completed_at)
                            <dt class="col-5 text-muted">{{ __('Terminé le') }}</dt>
                            <dd class="col-7 text-success fw-semibold">{{ $instance->completed_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ── Pause Note Modal ────────────────────────────────── --}}
@if($instance->status === 'running')
<div class="modal fade act-modal" id="pauseNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bi bi-pause-circle-fill"></i>
                    {{ __('Mettre en pause') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('workflows.instances.pause', $instance) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-3 text-muted small">{{ __('Indiquez la raison de la pause pour assurer la traçabilité.') }}</p>
                    <div>
                        <label for="pause_note" class="form-label fw-semibold" style="font-size:.88rem;">
                            {{ __('Motif de la pause') }}
                            <span class="text-muted fw-normal">({{ __('optionnel') }})</span>
                        </label>
                        <textarea id="pause_note" name="pause_note" class="form-control" rows="3"
                                  placeholder="{{ __('Ex: En attente de la signature du directeur…') }}"
                                  style="border-radius:10px;font-size:.87rem;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-warning d-flex align-items-center gap-2">
                        <i class="bi bi-pause-circle-fill"></i> {{ __('Confirmer la pause') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
