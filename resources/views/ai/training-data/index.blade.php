@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('training_data') }}</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> {{ __('refresh') }}
                        </button>
                        <a href="{{ route('ai.training-data.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> {{ __('add_training_data') }}
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
                            <select class="form-select" id="actionTypeFilter">
                                <option value="">{{ __('all_action_types') }}</option>
                                @foreach($actionTypes as $actionType)
                                    <option value="{{ $actionType->id }}">{{ $actionType->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="validationFilter">
                                <option value="">{{ __('all_validation_status') }}</option>
                                <option value="1">{{ __('validated') }}</option>
                                <option value="0">{{ __('not_validated') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sortOrder">
                                <option value="newest">{{ __('newest_first') }}</option>
                                <option value="oldest">{{ __('oldest_first') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau des données -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('id') }}</th>
                                    <th>{{ __('action_type') }}</th>
                                    <th>{{ __('input') }}</th>
                                    <th>{{ __('expected_output') }}</th>
                                    <th>{{ __('validation_status') }}</th>
                                    <th>{{ __('created_by') }}</th>
                                    <th>{{ __('validated_by') }}</th>
                                    <th>{{ __('created_at') }}</th>
                                    <th>{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($trainingData as $data)
                                    <tr>
                                        <td>{{ $data->id }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $data->actionType->display_name }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $data->input }}">
                                                {{ $data->input }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $data->expected_output }}">
                                                {{ $data->expected_output }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $data->is_validated ? 'success' : 'warning' }}">
                                                {{ $data->is_validated ? __('validated') : __('not_validated') }}
                                            </span>
                                        </td>
                                        <td>{{ $data->creator->name }}</td>
                                        <td>{{ $data->validator ? $data->validator->name : '-' }}</td>
                                        <td>{{ $data->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('ai.training-data.show', $data) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('ai.training-data.edit', $data) }}" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteTrainingData({{ $data->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-3">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                                {{ __('no_training_data_found') }}
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $trainingData->links() }}
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
                {{ __('confirm_training_data_deletion') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtrage en temps réel
    const searchInput = document.getElementById('searchInput');
    const actionTypeFilter = document.getElementById('actionTypeFilter');
    const validationFilter = document.getElementById('validationFilter');
    const sortOrder = document.getElementById('sortOrder');
    const table = document.querySelector('table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const actionTypeValue = actionTypeFilter.value;
        const validationValue = validationFilter.value;
        const sortValue = sortOrder.value;

        let visibleRows = Array.from(rows).filter(row => {
            if (row.cells.length === 1) return true; // Garder la ligne "aucun résultat"

            const input = row.cells[2].textContent.toLowerCase();
            const output = row.cells[3].textContent.toLowerCase();
            const actionType = row.cells[1].textContent.trim();
            const validation = row.cells[4].textContent.trim();

            const matchesSearch = input.includes(searchTerm) || output.includes(searchTerm);
            const matchesActionType = !actionTypeValue || actionType.includes(actionTypeValue);
            const matchesValidation = !validationValue ||
                (validationValue === '1' && validation.includes('validated')) ||
                (validationValue === '0' && validation.includes('not_validated'));

            return matchesSearch && matchesActionType && matchesValidation;
        });

        // Trier les lignes
        if (sortValue === 'newest') {
            visibleRows.sort((a, b) => {
                const dateA = new Date(a.cells[7].textContent);
                const dateB = new Date(b.cells[7].textContent);
                return dateB - dateA;
            });
        } else {
            visibleRows.sort((a, b) => {
                const dateA = new Date(a.cells[7].textContent);
                const dateB = new Date(b.cells[7].textContent);
                return dateA - dateB;
            });
        }

        // Mettre à jour le tableau
        const tbody = table.getElementsByTagName('tbody')[0];
        tbody.innerHTML = '';
        visibleRows.forEach(row => tbody.appendChild(row.cloneNode(true)));

        // Afficher un message si aucun résultat
        if (visibleRows.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-3">
                        <div class="text-muted">
                            <i class="bi bi-search fs-4 d-block mb-2"></i>
                            {{ __('no_results_found') }}
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    searchInput.addEventListener('input', filterTable);
    actionTypeFilter.addEventListener('change', filterTable);
    validationFilter.addEventListener('change', filterTable);
    sortOrder.addEventListener('change', filterTable);
});

// Fonction de suppression
function deleteTrainingData(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const form = document.getElementById('deleteForm');
    form.action = `/ai/training-data/${id}`;
    modal.show();
}
</script>
@endpush

@push('styles')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.table th {
    font-weight: 600;
    color: #6c757d;
    background-color: #f8f9fa;
}

.badge {
    font-weight: 500;
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
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid rgba(0, 0, 0, 0.125);
}
</style>
@endpush
