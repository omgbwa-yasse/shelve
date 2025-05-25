@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('ai_interactions') }}</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> {{ __('refresh') }}
                        </button>
                        <a href="{{ route('ai.interactions.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> {{ __('add_interaction') }}
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
                            <select class="form-select" id="modelFilter">
                                <option value="">{{ __('all_models') }}</option>
                                @foreach($models as $model)
                                    <option value="{{ $model->id }}">{{ $model->name }} ({{ $model->provider }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">{{ __('all_statuses') }}</option>
                                <option value="success">{{ __('success') }}</option>
                                <option value="error">{{ __('error') }}</option>
                                <option value="pending">{{ __('pending') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sortFilter">
                                <option value="newest">{{ __('newest_first') }}</option>
                                <option value="oldest">{{ __('oldest_first') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau des interactions -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('model') }}</th>
                                    <th>{{ __('input') }}</th>
                                    <th>{{ __('output') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('duration') }}</th>
                                    <th>{{ __('created_at') }}</th>
                                    <th>{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($interactions as $interaction)
                                    <tr>
                                        <td>{{ $interaction->id }}</td>
                                        <td>{{ $interaction->model->name }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $interaction->input }}">
                                                {{ $interaction->input }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $interaction->output }}">
                                                {{ $interaction->output }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $interaction->status === 'success' ? 'success' : ($interaction->status === 'error' ? 'danger' : 'warning') }}">
                                                {{ __($interaction->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $interaction->duration }}ms</td>
                                        <td>{{ $interaction->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('ai.interactions.show', $interaction) }}"
                                                   class="btn btn-outline-primary"
                                                   title="{{ __('view') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('ai.interactions.edit', $interaction) }}"
                                                   class="btn btn-outline-secondary"
                                                   title="{{ __('edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-outline-danger"
                                                        title="{{ __('delete') }}"
                                                        onclick="deleteInteraction({{ $interaction->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0 text-muted">{{ __('no_interactions_found') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-4">
                        {{ $interactions->links() }}
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
                {{ __('delete_interaction_confirmation') }}
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
function deleteInteraction(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/ai/interactions/${id}`;
    modal.show();
}

// Filtrage en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const modelFilter = document.getElementById('modelFilter');
    const statusFilter = document.getElementById('statusFilter');
    const sortFilter = document.getElementById('sortFilter');
    const table = document.querySelector('table tbody');
    const rows = table.getElementsByTagName('tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const modelValue = modelFilter.value;
        const statusValue = statusFilter.value;
        const sortValue = sortFilter.value;

        Array.from(rows).forEach(row => {
            if (row.cells.length === 1) return; // Skip empty message row

            const modelCell = row.cells[1].textContent;
            const statusCell = row.cells[4].textContent.trim();
            const inputCell = row.cells[2].textContent.toLowerCase();
            const outputCell = row.cells[3].textContent.toLowerCase();

            const matchesSearch = inputCell.includes(searchTerm) || outputCell.includes(searchTerm);
            const matchesModel = !modelValue || modelCell.includes(modelValue);
            const matchesStatus = !statusValue || statusCell === statusValue;

            row.style.display = matchesSearch && matchesModel && matchesStatus ? '' : 'none';
        });

        // Trier les lignes
        const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
        visibleRows.sort((a, b) => {
            if (a.cells.length === 1 || b.cells.length === 1) return 0;
            const dateA = new Date(a.cells[6].textContent);
            const dateB = new Date(b.cells[6].textContent);
            return sortValue === 'newest' ? dateB - dateA : dateA - dateB;
        });

        // Réorganiser les lignes dans le tableau
        visibleRows.forEach(row => table.appendChild(row));
    }

    searchInput.addEventListener('input', filterTable);
    modelFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
    sortFilter.addEventListener('change', filterTable);
});
</script>
@endpush
