@extends('layouts.app')

@push('styles')
<style>
    /* ── KPI Cards ─────────────────────────────────────────── */
    .task-kpi-card {
        border: none;
        border-radius: 14px;
        padding: 18px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,.07);
        transition: transform .18s, box-shadow .18s;
        text-decoration: none;
        color: inherit;
    }
    .task-kpi-card:hover { transform: translateY(-3px); box-shadow: 0 6px 22px rgba(0,0,0,.12); }
    .kpi-icon {
        width: 52px; height: 52px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
    .kpi-pending   .kpi-icon { background: #fff3cd; color: #b07c00; }
    .kpi-inprogress .kpi-icon { background: #cfe2ff; color: #084298; }
    .kpi-done      .kpi-icon { background: #d1e7dd; color: #0a3622; }
    .kpi-urgent    .kpi-icon { background: #f8d7da; color: #842029; }
    .kpi-number { font-size: 1.8rem; font-weight: 700; line-height: 1; }
    .kpi-label  { font-size: .78rem; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; margin-top: 2px; }

    /* ── Filter bar ─────────────────────────────────────────── */
    .filter-bar {
        background: #fff;
        border-radius: 12px;
        padding: 14px 18px;
        box-shadow: 0 1px 8px rgba(0,0,0,.06);
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: flex-end;
    }

    /* ── Table ───────────────────────────────────────────────── */
    .tasks-table { border-collapse: separate; border-spacing: 0; }
    .tasks-table thead th {
        background: #f8f9fa;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6c757d;
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
        padding: 10px 14px;
        white-space: nowrap;
    }
    .tasks-table tbody tr {
        border-bottom: 1px solid #f1f3f5;
        transition: background .12s;
    }
    .tasks-table tbody tr:hover { background: #fafbfc; }
    .tasks-table tbody td { padding: 12px 14px; vertical-align: middle; }
    .tasks-table tbody tr.overdue-row td:first-child { border-left: 3px solid #dc3545; }

    /* ── Type pill ───────────────────────────────────────────── */
    .type-pill {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: .72rem; font-weight: 600; padding: 3px 9px;
        border-radius: 20px; white-space: nowrap;
    }
    .type-workflow { background: #e0f0ff; color: #0553b1; }
    .type-general  { background: #f0f0f4; color: #555; }

    /* ── Status/Priority pills ───────────────────────────────── */
    .status-pill {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: .75rem; font-weight: 600; padding: 4px 10px;
        border-radius: 20px;
    }
    .s-pending    { background: #fff3cd; color: #856404; }
    .s-inprogress { background: #cfe2ff; color: #084298; }
    .s-completed  { background: #d1e7dd; color: #0a3622; }
    .s-cancelled  { background: #f8d7da; color: #842029; }

    .p-urgent  { background: #f8d7da; color: #842029; }
    .p-high    { background: #fff3cd; color: #856404; }
    .p-normal  { background: #e2e3e5; color: #383d41; }
    .p-low     { background: #d1ecf1; color: #0c5460; }

    /* ── Late badge ──────────────────────────────────────────── */
    .late-badge {
        font-size: .68rem; font-weight: 700; padding: 2px 7px;
        border-radius: 20px; background: #dc3545; color: #fff;
        margin-left: 6px;
    }

    /* ── Empty state ─────────────────────────────────────────── */
    .empty-state { padding: 60px 20px; text-align: center; }
    .empty-state .empty-icon { font-size: 4rem; color: #dee2e6; margin-bottom: 16px; }

    /* ── Action buttons ──────────────────────────────────────── */
    .action-btn {
        width: 30px; height: 30px;
        border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .85rem; border: none; cursor: pointer;
        transition: opacity .15s;
    }
    .action-btn:hover { opacity: .8; }
    .ab-view    { background: #e0f0ff; color: #0553b1; }
    .ab-edit    { background: #fff8e1; color: #c77700; }
    .ab-check   { background: #d1e7dd; color: #086b3f; }
    .ab-del     { background: #f8d7da; color: #842029; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- ── Header ───────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold d-flex align-items-center gap-2">
                <i class="bi bi-check2-square text-primary"></i>
                {{ __('Tâches') }}
            </h2>
            <p class="text-muted small mb-0">{{ __('Toutes les tâches — indépendantes et issues de processus') }}</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-lg"></i> {{ __('Nouvelle tâche') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── KPI Cards ─────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('tasks.index', ['status' => 'pending']) }}" class="task-kpi-card kpi-pending d-flex">
                <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="kpi-number">{{ $tasks->where('status', 'pending')->count() }}</div>
                    <div class="kpi-label">{{ __('En attente') }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('tasks.index', ['status' => 'in_progress']) }}" class="task-kpi-card kpi-inprogress d-flex">
                <div class="kpi-icon"><i class="bi bi-arrow-repeat"></i></div>
                <div>
                    <div class="kpi-number">{{ $tasks->where('status', 'in_progress')->count() }}</div>
                    <div class="kpi-label">{{ __('En cours') }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('tasks.index', ['status' => 'completed']) }}" class="task-kpi-card kpi-done d-flex">
                <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="kpi-number">{{ $tasks->where('status', 'completed')->count() }}</div>
                    <div class="kpi-label">{{ __('Terminées') }}</div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('tasks.index', ['priority' => 'urgent']) }}" class="task-kpi-card kpi-urgent d-flex">
                <div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="kpi-number">{{ $tasks->where('priority', 'urgent')->count() }}</div>
                    <div class="kpi-label">{{ __('Urgentes') }}</div>
                </div>
            </a>
        </div>
    </div>

    {{-- ── Filter Bar ────────────────────────────────── --}}
    <div class="filter-bar mb-4">
        <form method="GET" action="{{ route('tasks.index') }}" class="d-flex flex-wrap gap-2 align-items-end w-100">
            <div>
                <label class="form-label small fw-semibold mb-1">{{ __('Statut') }}</label>
                <select name="status" class="form-select form-select-sm" style="min-width:140px">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    <option value="pending"     {{ request('status') == 'pending'      ? 'selected' : '' }}>{{ __('En attente') }}</option>
                    <option value="in_progress" {{ request('status') == 'in_progress'  ? 'selected' : '' }}>{{ __('En cours') }}</option>
                    <option value="completed"   {{ request('status') == 'completed'    ? 'selected' : '' }}>{{ __('Terminées') }}</option>
                    <option value="cancelled"   {{ request('status') == 'cancelled'    ? 'selected' : '' }}>{{ __('Annulées') }}</option>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold mb-1">{{ __('Priorité') }}</label>
                <select name="priority" class="form-select form-select-sm" style="min-width:130px">
                    <option value="">{{ __('Toutes') }}</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>{{ __('Urgente') }}</option>
                    <option value="high"   {{ request('priority') == 'high'   ? 'selected' : '' }}>{{ __('Haute') }}</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>{{ __('Normale') }}</option>
                    <option value="low"    {{ request('priority') == 'low'    ? 'selected' : '' }}>{{ __('Basse') }}</option>
                </select>
            </div>
            <div>
                <label class="form-label small fw-semibold mb-1">{{ __('Assigné à') }}</label>
                <select name="assigned_to" class="form-select form-select-sm" style="min-width:130px">
                    <option value="">{{ __('Tous') }}</option>
                    <option value="me" {{ request('assigned_to') == 'me' ? 'selected' : '' }}>{{ __('Mes tâches') }}</option>
                </select>
            </div>
            <div class="d-flex gap-2 ms-auto">
                <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-funnel"></i> {{ __('Filtrer') }}
                </button>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-x-lg"></i> {{ __('Réinitialiser') }}
                </a>
            </div>
        </form>
    </div>

    {{-- ── Task List ─────────────────────────────────── --}}
    <div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
        @if($tasks->count() > 0)
            <div class="table-responsive">
                <table class="table tasks-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Tâche') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Priorité') }}</th>
                            <th>{{ __('Assignée à') }}</th>
                            <th>{{ __('Échéance') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr class="{{ $task->isOverdue ? 'overdue-row' : '' }}">
                                {{-- Title --}}
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none text-dark fw-semibold">
                                        {{ $task->title }}
                                    </a>
                                    @if($task->isOverdue)
                                        <span class="late-badge">{{ __('En retard') }}</span>
                                    @endif
                                </td>
                                {{-- Type --}}
                                <td>
                                    @if($task->isWorkflowTask)
                                        <span class="type-pill type-workflow">
                                            <i class="bi bi-diagram-3"></i> {{ __('Processus') }}
                                        </span>
                                    @else
                                        <span class="type-pill type-general">
                                            <i class="bi bi-person-workspace"></i> {{ __('Générale') }}
                                        </span>
                                    @endif
                                </td>
                                {{-- Status --}}
                                <td>
                                    @switch($task->status)
                                        @case('pending')
                                            <span class="status-pill s-pending"><i class="bi bi-hourglass-split"></i> {{ __('En attente') }}</span>
                                            @break
                                        @case('in_progress')
                                            <span class="status-pill s-inprogress"><i class="bi bi-arrow-repeat"></i> {{ __('En cours') }}</span>
                                            @break
                                        @case('completed')
                                            <span class="status-pill s-completed"><i class="bi bi-check-circle"></i> {{ __('Terminée') }}</span>
                                            @break
                                        @case('cancelled')
                                            <span class="status-pill s-cancelled"><i class="bi bi-x-circle"></i> {{ __('Annulée') }}</span>
                                            @break
                                    @endswitch
                                </td>
                                {{-- Priority --}}
                                <td>
                                    @switch($task->priority)
                                        @case('urgent') <span class="status-pill p-urgent"><i class="bi bi-exclamation-triangle"></i> {{ __('Urgente') }}</span> @break
                                        @case('high')   <span class="status-pill p-high">{{ __('Haute') }}</span> @break
                                        @case('normal') <span class="status-pill p-normal">{{ __('Normale') }}</span> @break
                                        @case('low')    <span class="status-pill p-low">{{ __('Basse') }}</span> @break
                                    @endswitch
                                </td>
                                {{-- Assigned --}}
                                <td>
                                    @if($task->assignedUser)
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:26px;height:26px;border-radius:50%;background:#e0f0ff;color:#0553b1;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0;">
                                                {{ strtoupper(substr($task->assignedUser->name, 0, 1)) }}
                                            </div>
                                            <span class="small">{{ $task->assignedUser->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted small fst-italic">{{ __('Non assignée') }}</span>
                                    @endif
                                </td>
                                {{-- Due Date --}}
                                <td class="{{ $task->isOverdue ? 'text-danger fw-semibold' : 'text-muted' }} small">
                                    @if($task->due_date)
                                        <i class="bi bi-calendar3 me-1"></i>{{ $task->due_date->format('d/m/Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                {{-- Actions --}}
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        @if($task->status != 'completed')
                                            <button type="button"
                                                class="action-btn ab-check"
                                                title="{{ __('Marquer comme terminée') }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#bulkCompleteModal"
                                                data-task-id="{{ $task->id }}"
                                                data-task-title="{{ addslashes($task->title) }}"
                                                data-task-url="{{ route('tasks.complete', $task) }}">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        @endif

                                        <a href="{{ route('tasks.show', $task) }}" class="action-btn ab-view" title="{{ __('Voir') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($task->status != 'completed')
                                            <a href="{{ route('tasks.edit', $task) }}" class="action-btn ab-edit" title="{{ __('Modifier') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('{{ __('Supprimer cette tâche ?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn ab-del" title="{{ __('Supprimer') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-center py-3 border-top">
                {{ $tasks->links() }}
            </div>

        @else
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-check2-square"></i></div>
                <h5 class="fw-semibold text-muted">{{ __('Aucune tâche trouvée') }}</h5>
                <p class="text-muted small">{{ __('Créez votre première tâche ou ajustez les filtres.') }}</p>
                <a href="{{ route('tasks.create') }}" class="btn btn-primary mt-2">
                    <i class="bi bi-plus-lg me-1"></i> {{ __('Créer une tâche') }}
                </a>
            </div>
        @endif
    </div>

</div>

{{-- ── Global Quick-Complete Modal ──────────────────────── --}}
<div class="modal fade" id="bulkCompleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#198754,#0b5e31);color:#fff;">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ __('Terminer la tâche') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <form id="bulkCompleteForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-3" style="font-size:.92rem;">
                        {{ __('Terminer') }}
                        <strong id="bulkTaskTitle"></strong> ?
                    </p>
                    <div>
                        <label for="bulk_completion_note" class="form-label fw-semibold" style="font-size:.88rem;">
                            {{ __('Note de clôture') }}
                            <span class="text-muted fw-normal">({{ __('optionnel') }})</span>
                        </label>
                        <textarea
                            id="bulk_completion_note"
                            name="completion_note"
                            class="form-control"
                            rows="3"
                            placeholder="{{ __('Ex: Rapport envoyé, validé par la direction…') }}"
                            style="border-radius:10px;font-size:.87rem;"></textarea>
                        <div class="form-text mt-1">
                            <i class="bi bi-lightbulb me-1 text-warning"></i>
                            {{ __('Cette note sera enregistrée comme commentaire de traçabilité.') }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                    <button type="submit" class="btn btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i> {{ __('Confirmer et terminer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('bulkCompleteModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function (e) {
        const btn   = e.relatedTarget;
        const title = btn.dataset.taskTitle;
        const url   = btn.dataset.taskUrl;
        document.getElementById('bulkTaskTitle').textContent = '« ' + title + ' »';
        document.getElementById('bulkCompleteForm').action = url;
        document.getElementById('bulk_completion_note').value = '';
    });
});
</script>
@endpush

@endsection
