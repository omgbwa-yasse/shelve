@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('ai_actions') }}</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> {{ __('refresh') }}
                        </button>
                        <a href="{{ route('ai.actions.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> {{ __('add_action') }}
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
                            <select class="form-select" id="typeFilter">
                                <option value="">{{ __('all_types') }}</option>
                                @foreach($actionTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">{{ __('all_statuses') }}</option>
                                <option value="active">{{ __('active') }}</option>
                                <option value="inactive">{{ __('inactive') }}</option>
                                <option value="draft">{{ __('draft') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sortFilter">
                                <option value="newest">{{ __('newest_first') }}</option>
                                <option value="oldest">{{ __('oldest_first') }}</option>
                                <option value="name">{{ __('name') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau des actions -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('name') }}</th>
                                    <th>{{ __('type') }}</th>
                                    <th>{{ __('description') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('last_run') }}</th>
                                    <th>{{ __('created_at') }}</th>
                                    <th>{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($actions as $action)
                                    <tr>
                                        <td>{{ $action->id }}</td>
                                        <td>{{ $action->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $action->type->color ?? 'secondary' }}">
                                                {{ $action->type->name }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $action->description }}">
                                                {{ $action->description }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $action->status === 'active' ? 'success' :
                                                ($action->status === 'inactive' ? 'warning' : 'secondary') }}">
                                                {{ __($action->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($action->last_run_at)
                                                <span title="{{ $action->last_run_at->format('Y-m-d H:i:s') }}">
                                                    {{ $action->last_run_at->diffForHumans() }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $action->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('ai.actions.show', $action) }}"
                                                   class="btn btn-outline-primary"
                                                   title="{{ __('view') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('ai.actions.edit', $action) }}"
                                                   class="btn btn-outline-secondary"
                                                   title="{{ __('edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($action->status === 'active')
                                                    <button type="button"
                                                            class="btn btn-outline-success"
                                                            title="{{ __('run') }}"
                                                            onclick="runAction({{ $action->id }})">
                                                        <i class="bi bi-play-fill"></i>
                                                    </button>
                                                @endif
                                                <button type="button"
                                                        class="btn btn-outline-danger"
                                                        title="{{ __('delete') }}"
                                                        onclick="deleteAction({{ $action->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0 text-muted">{{ __('no_actions_found') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-4">
                        {{ $actions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('confirm_deletion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('delete_action_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('cancel') }}
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        {{ __('delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation d'exécution -->
<div class="modal fade" id="runModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('confirm_execution') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('run_action_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('cancel') }}
                </button>
                <form id="runForm" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        {{ __('run') }}
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

.text-truncate {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
function deleteAction(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/ai/actions/${id}`;
    modal.show();
}

function runAction(id) {
    const modal = new bootstrap.Modal(document.getElementById('runModal'));
    const form = document.getElementById('runForm');
    form.action = `/ai/actions/${id}/run`;
    modal.show();
}

// Filtrage en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const sortFilter = document.getElementById('sortFilter');
    const table = document.querySelector('table tbody');
    const rows = table.getElementsByTagName('tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const typeValue = typeFilter.value;
        const statusValue = statusFilter.value;
        const sortValue = sortFilter.value;

        Array.from(rows).forEach(row => {
            if (row.cells.length === 1) return; // Skip empty message row

            const nameCell = row.cells[1].textContent.toLowerCase();
            const typeCell = row.cells[2].textContent.trim();
            const statusCell = row.cells[4].textContent.trim();
            const descriptionCell = row.cells[3].textContent.toLowerCase();

            const matchesSearch = nameCell.includes(searchTerm) || descriptionCell.includes(searchTerm);
            const matchesType = !typeValue || typeCell === typeValue;
            const matchesStatus = !statusValue || statusCell === statusValue;

            row.style.display = matchesSearch && matchesType && matchesStatus ? '' : 'none';
        });

        // Trier les lignes
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        visibleRows.sort((a, b) => {
            if (a.cells.length === 1 || b.cells.length === 1) return 0;

            if (sortValue === 'name') {
                return a.cells[1].textContent.localeCompare(b.cells[1].textContent);
            }

            const dateA = new Date(a.cells[6].textContent);
            const dateB = new Date(b.cells[6].textContent);
            return sortValue === 'newest' ? dateB - dateA : dateA - dateB;
        });

        // Réorganiser les lignes dans le tableau
        visibleRows.forEach(row => table.appendChild(row));
    }

    searchInput.addEventListener('input', filterTable);
    typeFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
    sortFilter.addEventListener('change', filterTable);
});
</script>
@endpush
