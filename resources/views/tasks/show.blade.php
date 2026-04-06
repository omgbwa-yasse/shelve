@extends('layouts.app')

@push('styles')
<style>
    /* ── Main layout ────────────────────────────────────────── */
    .task-show-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,.05);
        overflow: hidden;
        margin-bottom: 20px;
    }
    .tsc-header {
        padding: 18px 22px;
        border-bottom: 1px solid #f0f2f5;
        display: flex; align-items: center; gap: 10px;
    }
    .tsc-header h5 { margin: 0; font-size: .93rem; font-weight: 700; }
    .tsc-body { padding: 20px 22px; }

    /* ── Status / Priority pills ─────────────────────────────── */
    .s-pill { display:inline-flex;align-items:center;gap:5px;font-size:.8rem;font-weight:700;padding:5px 12px;border-radius:20px; }
    .sp { background:#fff3cd;color:#856404; }
    .sip{ background:#cfe2ff;color:#084298; }
    .sc { background:#d1e7dd;color:#0a3622; }
    .sx { background:#f8d7da;color:#842029; }
    .pu { background:#f8d7da;color:#842029; }
    .ph { background:#fff3cd;color:#856404; }
    .pn { background:#e2e3e5;color:#383d41; }
    .pl { background:#d1ecf1;color:#0c5460; }

    /* ── Comment thread ─────────────────────────────────────── */
    .comment-item {
        display: flex; gap: 12px; margin-bottom: 16px;
    }
    .comment-avatar {
        width: 34px; height: 34px; min-width: 34px;
        border-radius: 50%;
        background: #e0f0ff; color: #0553b1;
        font-weight: 700; font-size: .85rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .comment-avatar.av-completion { background: #d1e7dd; color: #0a3622; }
    .comment-bubble {
        flex: 1;
        background: #f8f9fa;
        border-radius: 0 10px 10px 10px;
        padding: 10px 14px;
        font-size: .87rem;
    }
    .comment-meta {
        font-size: .73rem;
        color: #9ca3af;
        margin-bottom: 4px;
        display: flex; align-items: center; gap: 8px;
    }
    .comment-text { color: #374151; line-height: 1.5; }

    /* ── Add comment form ────────────────────────────────────── */
    .add-comment-form {
        display: flex; gap: 10px; align-items: flex-start;
        border-top: 1px solid #f0f2f5;
        padding-top: 14px; margin-top: 6px;
    }
    .add-comment-form textarea {
        flex: 1; border-radius: 10px; resize: none; font-size: .87rem;
    }

    /* ── Action sidebar buttons ──────────────────────────────── */
    .action-btn-full {
        width: 100%; padding: 10px 16px;
        border-radius: 10px; border: none; cursor: pointer;
        font-weight: 600; font-size: .88rem;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: opacity .15s;
        text-decoration: none;
    }
    .action-btn-full:hover { opacity: .85; }
    .ab-complete { background: #d1e7dd; color: #0a3622; }
    .ab-edit     { background: #fff8e1; color: #c77700; }
    .ab-delete   { background: #f8d7da; color: #842029; }

    /* ── Timeline history ─────────────────────────────────────── */
    .history-item {
        display: flex; gap: 10px;
        padding: 6px 0; font-size: .82rem;
        border-bottom: 1px solid #f1f3f5;
    }
    .history-item:last-child { border-bottom: none; }
    .history-dot {
        width: 8px; height: 8px; min-width: 8px;
        border-radius: 50%; background: #0d6efd;
        margin-top: 5px;
    }

    /* ── Completion modal ────────────────────────────────────── */
    .modal-completion .modal-header {
        background: linear-gradient(135deg, #198754, #0b5e31);
        color: #fff;
        border-radius: 12px 12px 0 0;
    }
    .modal-completion .modal-content { border-radius: 12px; }
    .modal-completion .btn-close { filter: invert(1); }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Header ─────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted small mb-1 d-flex align-items-center gap-1">
                <a href="{{ route('tasks.index') }}" class="text-decoration-none text-muted">
                    <i class="bi bi-check2-square"></i> {{ __('Tâches') }}
                </a>
                <i class="bi bi-chevron-right" style="font-size:.7rem;"></i>
                {{ Str::limit($task->title, 40) }}
            </p>
            <h2 class="fw-bold mb-0 d-flex align-items-center gap-2">
                {{ $task->title }}
                @if($task->isOverdue)
                    <span class="badge bg-danger" style="font-size:.7rem;">{{ __('En retard') }}</span>
                @endif
            </h2>
        </div>
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ── Left column ────────────────────────────── --}}
        <div class="col-lg-8">

            {{-- Info card --}}
            <div class="task-show-card">
                <div class="tsc-header">
                    <i class="bi bi-info-circle text-primary"></i>
                    <h5>{{ __('Détails de la tâche') }}</h5>
                    @switch($task->status)
                        @case('pending')    <span class="s-pill sp ms-auto"><i class="bi bi-hourglass-split"></i>{{ __('En attente') }}</span> @break
                        @case('in_progress')<span class="s-pill sip ms-auto"><i class="bi bi-arrow-repeat"></i>{{ __('En cours') }}</span> @break
                        @case('completed')  <span class="s-pill sc ms-auto"><i class="bi bi-check-circle"></i>{{ __('Terminée') }}</span> @break
                        @case('cancelled')  <span class="s-pill sx ms-auto"><i class="bi bi-x-circle"></i>{{ __('Annulée') }}</span> @break
                    @endswitch
                </div>
                <div class="tsc-body">
                    @if($task->description)
                        <p style="color:#374151;line-height:1.7;">{{ $task->description }}</p>
                        <hr class="my-3">
                    @endif

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">{{ __('Priorité') }}</div>
                            @switch($task->priority)
                                @case('urgent') <span class="s-pill pu"><i class="bi bi-exclamation-triangle"></i>{{ __('Urgente') }}</span> @break
                                @case('high')   <span class="s-pill ph">{{ __('Haute') }}</span> @break
                                @case('normal') <span class="s-pill pn">{{ __('Normale') }}</span> @break
                                @case('low')    <span class="s-pill pl">{{ __('Basse') }}</span> @break
                            @endswitch
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">{{ __('Type') }}</div>
                            @if($task->isWorkflowTask)
                                <span class="s-pill" style="background:#e0f0ff;color:#0553b1;">
                                    <i class="bi bi-diagram-3"></i>{{ __('Tâche de processus') }}
                                </span>
                                @if($task->workflowInstance)
                                    <br><small class="text-muted mt-1 d-block">
                                        <a href="{{ route('workflows.instances.show', $task->workflowInstance) }}" class="text-decoration-none">
                                            {{ $task->workflowInstance->name }}
                                        </a>
                                    </small>
                                @endif
                            @else
                                <span class="s-pill pn"><i class="bi bi-person-workspace"></i>{{ __('Tâche générale') }}</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">{{ __('Assignée à') }}</div>
                            @if($task->assignedUser)
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:28px;height:28px;border-radius:50%;background:#e0f0ff;color:#0553b1;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;">
                                        {{ strtoupper(substr($task->assignedUser->name, 0, 1)) }}
                                    </div>
                                    <span class="fw-semibold small">{{ $task->assignedUser->name }}</span>
                                </div>
                            @else
                                <span class="text-muted small fst-italic">{{ __('Non assignée') }}</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">{{ __('Date limite') }}</div>
                            <span class="{{ $task->isOverdue ? 'text-danger fw-semibold' : '' }} small">
                                @if($task->due_date)
                                    <i class="bi bi-calendar3 me-1"></i>{{ $task->due_date->format('d/m/Y') }}
                                    @if($task->isOverdue) <span class="badge bg-danger ms-1" style="font-size:.62rem;">{{ __('En retard') }}</span> @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </span>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">{{ __('Créée par') }}</div>
                            <span class="small">{{ $task->creator->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">{{ __('Créée le') }}</div>
                            <span class="small">{{ $task->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($task->completed_at)
                            <div class="col-sm-6">
                                <div class="small text-muted mb-1">{{ __('Terminée le') }}</div>
                                <span class="small text-success fw-semibold">{{ $task->completed_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Comments --}}
            <div class="task-show-card">
                <div class="tsc-header">
                    <i class="bi bi-chat-left-text text-primary"></i>
                    <h5>{{ __('Notes & Commentaires') }}</h5>
                    <span class="ms-auto badge" style="background:#e0f0ff;color:#0553b1;">
                        {{ $task->comments->count() }}
                    </span>
                </div>
                <div class="tsc-body">
                    {{-- Existing comments --}}
                    @forelse($task->comments as $comment)
                        <div class="comment-item">
                            <div class="comment-avatar {{ str_starts_with($comment->comment, '✅') ? 'av-completion' : '' }}">
                                {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="comment-bubble">
                                <div class="comment-meta">
                                    <span class="fw-semibold text-dark">{{ $comment->user->name ?? 'N/A' }}</span>
                                    <span>{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                    @if($comment->isEdited)
                                        <span class="badge" style="background:#e2e3e5;color:#555;font-size:.65rem;">{{ __('modifié') }}</span>
                                    @endif
                                </div>
                                <div class="comment-text">{{ $comment->comment }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-4 mb-0" style="font-size:.87rem;">
                            <i class="bi bi-chat-square-dots d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
                            {{ __('Aucun commentaire pour l\'instant.') }}
                        </p>
                    @endforelse

                    {{-- Add comment --}}
                    @if($task->status !== 'completed' && $task->status !== 'cancelled')
                        <form action="{{ route('tasks.comments.store', $task) }}" method="POST" class="add-comment-form">
                            @csrf
                            <div class="comment-avatar">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <textarea name="comment" rows="2" class="form-control"
                                          placeholder="{{ __('Ajoutez une note ou un commentaire…') }}"
                                          required style="border-radius:10px; font-size:.87rem;"></textarea>
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="bi bi-send me-1"></i>{{ __('Envoyer') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── Right sidebar ───────────────────────── --}}
        <div class="col-lg-4">

            {{-- Actions --}}
            <div class="task-show-card mb-3">
                <div class="tsc-header">
                    <i class="bi bi-lightning-charge text-warning"></i>
                    <h5>{{ __('Actions') }}</h5>
                </div>
                <div class="tsc-body d-flex flex-column gap-2">
                    @if($task->status !== 'completed' && $task->status !== 'cancelled')
                        {{-- Completion triggers modal --}}
                        <button type="button" class="action-btn-full ab-complete"
                                data-bs-toggle="modal" data-bs-target="#completionModal">
                            <i class="bi bi-check-circle-fill fs-5"></i> {{ __('Marquer comme terminée') }}
                        </button>
                        <a href="{{ route('tasks.edit', $task) }}" class="action-btn-full ab-edit">
                            <i class="bi bi-pencil"></i> {{ __('Modifier') }}
                        </a>
                    @endif
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                          onsubmit="return confirm('{{ __('Supprimer cette tâche ?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn-full ab-delete w-100">
                            <i class="bi bi-trash"></i> {{ __('Supprimer') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Watchers --}}
            @if($task->watchers->count() > 0)
                <div class="task-show-card mb-3">
                    <div class="tsc-header">
                        <i class="bi bi-eye text-secondary"></i>
                        <h5>{{ __('Observateurs') }}</h5>
                        <span class="ms-auto badge" style="background:#e0f0ff;color:#0553b1;">{{ $task->watchers->count() }}</span>
                    </div>
                    <div class="tsc-body">
                        @foreach($task->watchers as $watcher)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div style="width:28px;height:28px;border-radius:50%;background:#f0f0f4;color:#555;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;">
                                    {{ strtoupper(substr($watcher->user->name ?? '?', 0, 1)) }}
                                </div>
                                <span class="small">{{ $watcher->user->name ?? 'N/A' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- History --}}
            @if($task->history && $task->history->count() > 0)
                <div class="task-show-card">
                    <div class="tsc-header">
                        <i class="bi bi-clock-history text-secondary"></i>
                        <h5>{{ __('Historique') }}</h5>
                    </div>
                    <div class="tsc-body" style="padding-top:12px;">
                        @foreach($task->history->sortByDesc('changed_at')->take(8) as $history)
                            <div class="history-item">
                                <div class="history-dot"></div>
                                <div>
                                    <div class="fw-semibold" style="font-size:.8rem;">{{ $history->action }}</div>
                                    @if($history->field_changed)
                                        <div class="text-muted" style="font-size:.75rem;">
                                            {{ $history->field_changed }}:
                                            <del class="text-danger">{{ $history->old_value }}</del>
                                            → <ins class="text-success">{{ $history->new_value }}</ins>
                                        </div>
                                    @endif
                                    <div class="text-muted" style="font-size:.72rem;">
                                        {{ $history->changed_at->format('d/m H:i') }}
                                        · {{ $history->user->name ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

{{-- ── Completion Modal ────────────────────────────────── --}}
@if($task->status !== 'completed' && $task->status !== 'cancelled')
<div class="modal fade modal-completion" id="completionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ __('Terminer la tâche') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('tasks.complete', $task) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-3" style="font-size:.92rem;">
                        {{ __('Vous êtes sur le point de marquer') }}
                        <strong>« {{ $task->title }} »</strong>
                        {{ __('comme terminée.') }}
                    </p>
                    <div>
                        <label for="completion_note" class="form-label fw-semibold" style="font-size:.88rem;">
                            {{ __('Note de clôture') }}
                            <span class="text-muted fw-normal">({{ __('optionnel mais recommandé') }})</span>
                        </label>
                        <textarea
                            id="completion_note"
                            name="completion_note"
                            class="form-control"
                            rows="4"
                            placeholder="{{ __('Ex: Rapport transmis à la direction, validé par M. Dupont. Aucun point en suspens.') }}"
                            style="border-radius:10px; font-size:.87rem;"></textarea>
                        <div class="form-text mt-1">
                            <i class="bi bi-lightbulb me-1 text-warning"></i>
                            {{ __('Cette note sera enregistrée comme commentaire de traçabilité.') }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('Annuler') }}
                    </button>
                    <button type="submit" class="btn btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        {{ __('Confirmer et terminer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
