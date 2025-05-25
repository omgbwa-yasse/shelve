@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('ai_action_batches') }}</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> {{ __('refresh') }}
                        </button>
                        <a href="{{ route('ai.action-batches.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> {{ __('add_batch') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="{{ __('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">{{ __('all_statuses') }}</option>
                                <option value="pending">{{ __('pending') }}</option>
                                <option value="running">{{ __('running') }}</option>
                                <option value="completed">{{ __('completed') }}</option>
                                <option value="failed">{{ __('failed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="priorityFilter">
                                <option value="">{{ __('all_priorities') }}</option>
                                <option value="high">{{ __('high') }}</option>
                                <option value="medium">{{ __('medium') }}</option>
                                <option value="low">{{ __('low') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sortFilter">
                                <option value="newest">{{ __('newest_first') }}</option>
                                <option value="oldest">{{ __('oldest_first') }}</option>
                                <option value="priority">{{ __('priority') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau des lots -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('name') }}</th>
                                    <th>{{ __('actions_count') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('priority') }}</th>
                                    <th>{{ __('progress') }}</th>
                                    <th>{{ __('started_at') }}</th>
                                    <th>{{ __('completed_at') }}</th>
                                    <th>{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batches as $batch)
                                    <tr>
                                        <td>{{ $batch->id }}</td>
                                        <td>{{ $batch->name }}</td>
                                        <td>{{ $batch->actions_count }}</td>
                                        <td>
                                            <span class="badge bg-{{ $batch->status === 'completed' ? 'success' :
                                                ($batch->status === 'running' ? 'primary' :
                                                ($batch->status === 'failed' ? 'danger' : 'warning')) }}">
                                                {{ __($batch->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $batch->priority === 'high' ? 'danger' :
                                                ($batch->priority === 'medium' ? 'warning' : 'info') }}">
                                                {{ __($batch->priority) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($batch->status === 'running')
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                         role="progressbar"
                                                         style="width: {{ $batch->progress }}%"
                                                         aria-valuenow="{{ $batch->progress }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                        {{ $batch->progress }}%
                                                    </div>
                                                </div>
                                            @elseif($batch->status === 'completed')
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success"
                                                         role="progressbar"
                                                         style="width: 100%"
                                                         aria-valuenow="100"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                        100%
                                                    </div>
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($batch->started_at)
                                                <span title="{{ $batch->started_at->format('Y-m-d H:i:s') }}">
                                                    {{ $batch->started_at->diffForHumans() }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($batch->completed_at)
                                                <span title="{{ $batch->completed_at->format('Y-m-d H:i:s') }}">
                                                    {{ $batch->completed_at->diffForHumans() }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('ai.action-batches.show', $batch) }}"
                                                   class="btn btn-outline-primary"
                                                   title="{{ __('view') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                @if($batch->status === 'pending')
                                                    <button type="button"
                                                            class="btn btn-outline-success"
                                                            title="{{ __('start') }}"
                                                            onclick="startBatch({{ $batch->id }})">
                                                        <i class="bi bi-play-fill"></i>
                                                    </button>
                                                @endif
                                                @if($batch->status === 'running')
                                                    <button type="button"
                                                            class="btn btn-outline-warning"
                                                            title="{{ __('pause') }}"
                                                            onclick="pauseBatch({{ $batch->id }})">
                                                        <i class="bi bi-pause-fill"></i>
                                                    </button>
                                                @endif
                                                @if(in_array($batch->status, ['pending', 'paused']))
                                                    <button type="button"
                                                            class="btn btn-outline-danger"
                                                            title="{{ __('cancel') }}"
                                                            onclick="cancelBatch({{ $batch->id }})">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0 text-muted">{{ __('no_batches_found') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-4">
                        {{ $batches->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de démarrage -->
<div class="modal fade" id="startModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('confirm_start') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('start_batch_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('cancel') }}
                </button>
                <form id="startForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        {{ __('start') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de pause -->
<div class="modal fade" id="pauseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('confirm_pause') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('pause_batch_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('cancel') }}
                </button>
                <form id="pauseForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        {{ __('pause') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation d'annulation -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('confirm_cancellation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('cancel_batch_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('cancel') }}
                </button>
                <form id="cancelForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        {{ __('confirm_cancellation') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table th {
    font-weight: 500;
    background-color: #f8f9fa;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.progress {
    background-color: #e9ecef;
    border-radius: 0.25rem;
}

.progress-bar {
    font-size: 0.75rem;
    font-weight: 500;
    line-height: 20px;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
}
</style>
@endpush

@push('scripts')
<script>
function startBatch(id) {
    const modal = new bootstrap.Modal(document.getElementById('startModal'));
    const form = document.getElementById('startForm');
    form.action = `/ai/action-batches/${id}/start`;
    modal.show();
}

function pauseBatch(id) {
    const modal = new bootstrap.Modal(document.getElementById('pauseModal'));
    const form = document.getElementById('pauseForm');
    form.action = `/ai/action-batches/${id}/pause`;
    modal.show();
}

function cancelBatch(id) {
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    const form = document.getElementById('cancelForm');
    form.action = `/ai/action-batches/${id}/cancel`;
    modal.show();
}

// Filtrage en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const sortFilter = document.getElementById('sortFilter');
    const table = document.querySelector('table tbody');
    const rows = table.getElementsByTagName('tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        const priorityValue = priorityFilter.value;
        const sortValue = sortFilter.value;

        Array.from(rows).forEach(row => {
            if (row.cells.length === 1) return; // Skip empty message row

            const nameCell = row.cells[1].textContent.toLowerCase();
            const statusCell = row.cells[3].textContent.trim();
            const priorityCell = row.cells[4].textContent.trim();

            const matchesSearch = nameCell.includes(searchTerm);
            const matchesStatus = !statusValue || statusCell === statusValue;
            const matchesPriority = !priorityValue || priorityCell === priorityValue;

            row.style.display = matchesSearch && matchesStatus && matchesPriority ? '' : 'none';
        });

        // Trier les lignes
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        visibleRows.sort((a, b) => {
            if (a.cells.length === 1 || b.cells.length === 1) return 0;

            if (sortValue === 'priority') {
                const priorityOrder = { high: 0, medium: 1, low: 2 };
                const priorityA = priorityOrder[a.cells[4].textContent.trim()] || 3;
                const priorityB = priorityOrder[b.cells[4].textContent.trim()] || 3;
                return priorityA - priorityB;
            }

            const dateA = new Date(a.cells[6].textContent);
            const dateB = new Date(b.cells[6].textContent);
            return sortValue === 'newest' ? dateB - dateA : dateA - dateB;
        });

        // Réorganiser les lignes dans le tableau
        visibleRows.forEach(row => table.appendChild(row));
    }

    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    priorityFilter.addEventListener('change', filterTable);
    sortFilter.addEventListener('change', filterTable);
});
</script>
@endpush
