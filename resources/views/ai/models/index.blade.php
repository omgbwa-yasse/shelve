@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('ai_models') }}</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> {{ __('refresh') }}
                        </button>
                        <a href="{{ route('ai.models.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> {{ __('add_model') }}
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
                                <option value="text">{{ __('text_model') }}</option>
                                <option value="image">{{ __('image_model') }}</option>
                                <option value="code">{{ __('code_model') }}</option>
                                <option value="embedding">{{ __('embedding_model') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">{{ __('all_statuses') }}</option>
                                <option value="active">{{ __('active') }}</option>
                                <option value="inactive">{{ __('inactive') }}</option>
                                <option value="training">{{ __('training') }}</option>
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

                    <!-- Tableau des modèles -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('name') }}</th>
                                    <th>{{ __('type') }}</th>
                                    <th>{{ __('version') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('performance') }}</th>
                                    <th>{{ __('last_trained') }}</th>
                                    <th>{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($models as $model)
                                    <tr>
                                        <td>{{ $model->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-{{ $model->type === 'text' ? 'chat-dots' :
                                                    ($model->type === 'image' ? 'image' :
                                                    ($model->type === 'code' ? 'code-square' : 'cpu')) }}
                                                    me-2 text-primary"></i>
                                                {{ $model->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ __($model->type . '_model') }}
                                            </span>
                                        </td>
                                        <td>{{ $model->version }}</td>
                                        <td>
                                            <span class="badge bg-{{ $model->status === 'active' ? 'success' :
                                                ($model->status === 'training' ? 'warning' : 'secondary') }}">
                                                {{ __($model->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($model->performance_metrics)
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 5px;">
                                                        <div class="progress-bar bg-success"
                                                             role="progressbar"
                                                             style="width: {{ $model->performance_metrics['accuracy'] ?? 0 }}%"
                                                             aria-valuenow="{{ $model->performance_metrics['accuracy'] ?? 0 }}"
                                                             aria-valuemin="0"
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">{{ number_format($model->performance_metrics['accuracy'] ?? 0, 1) }}%</small>
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $model->last_trained_at ? $model->last_trained_at->format('Y-m-d H:i:s') : '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('ai.models.show', $model) }}"
                                                   class="btn btn-outline-primary"
                                                   title="{{ __('view') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('ai.models.edit', $model) }}"
                                                   class="btn btn-outline-secondary"
                                                   title="{{ __('edit') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if($model->status === 'active')
                                                    <button type="button"
                                                            class="btn btn-outline-warning"
                                                            title="{{ __('train') }}"
                                                            onclick="trainModel({{ $model->id }})">
                                                        <i class="bi bi-lightning"></i>
                                                    </button>
                                                @endif
                                                <button type="button"
                                                        class="btn btn-outline-danger"
                                                        title="{{ __('delete') }}"
                                                        onclick="deleteModel({{ $model->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                            <p class="mt-2 mb-0 text-muted">{{ __('no_models_found') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-4">
                        {{ $models->links() }}
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
                {{ __('delete_model_confirmation') }}
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

<!-- Modal de confirmation d'entraînement -->
<div class="modal fade" id="trainModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('confirm_training') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('train_model_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('cancel') }}
                </button>
                <form id="trainForm" method="POST" style="display: inline;">
                    @csrf
                    @method('POST')
                    <button type="submit" class="btn btn-warning">
                        {{ __('start_training') }}
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
    margin-bottom: 0.25rem;
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
function deleteModel(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/ai/models/${id}`;
    modal.show();
}

function trainModel(id) {
    const modal = new bootstrap.Modal(document.getElementById('trainModal'));
    const form = document.getElementById('trainForm');
    form.action = `/ai/models/${id}/train`;
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

            const matchesSearch = nameCell.includes(searchTerm);
            const matchesType = !typeValue || typeCell.includes(typeValue);
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
