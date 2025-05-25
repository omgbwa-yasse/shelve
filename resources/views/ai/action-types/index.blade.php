@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-tag text-primary me-2"></i>
                            {{ __('ai_action_types') }}
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createActionTypeModal">
                            <i class="bi bi-plus-lg me-1"></i> {{ __('create_new') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" placeholder="{{ __('search') }}" id="searchInput">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="categoryFilter">
                                <option value="">{{ __('all_categories') }}</option>
                                <option value="text">{{ __('text') }}</option>
                                <option value="image">{{ __('image') }}</option>
                                <option value="code">{{ __('code') }}</option>
                                <option value="data">{{ __('data') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">{{ __('all_statuses') }}</option>
                                <option value="active">{{ __('active') }}</option>
                                <option value="inactive">{{ __('inactive') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau des types d'actions -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('id') }}</th>
                                    <th>{{ __('name') }}</th>
                                    <th>{{ __('category') }}</th>
                                    <th>{{ __('description') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('usage_count') }}</th>
                                    <th>{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($actionTypes as $type)
                                <tr>
                                    <td>{{ $type->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-medium">{{ $type->name }}</span>
                                            @if($type->is_system)
                                            <span class="badge bg-info ms-2">{{ __('system') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{
                                            $type->category === 'text' ? 'primary' :
                                            ($type->category === 'image' ? 'success' :
                                            ($type->category === 'code' ? 'warning' : 'info'))
                                        }}">
                                            {{ $type->category }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;">
                                            {{ $type->description }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $type->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ $type->status }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($type->usage_count) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewActionTypeModal{{ $type->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            @if(!$type->is_system)
                                            <a href="{{ route('ai.action-types.edit', $type) }}" class="btn btn-outline-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger"
                                                    onclick="deleteActionType({{ $type->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-tag fs-1 d-block mb-2"></i>
                                            {{ __('no_action_types_found') }}
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-4">
                        {{ $actionTypes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de création -->
<div class="modal fade" id="createActionTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('create_new_action_type') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('ai.action-types.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('name') }}</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('category') }}</label>
                        <select class="form-select" name="category" required>
                            <option value="text">{{ __('text') }}</option>
                            <option value="image">{{ __('image') }}</option>
                            <option value="code">{{ __('code') }}</option>
                            <option value="data">{{ __('data') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('description') }}</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('status') }}</label>
                        <select class="form-select" name="status" required>
                            <option value="active">{{ __('active') }}</option>
                            <option value="inactive">{{ __('inactive') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_system" id="isSystem">
                            <label class="form-check-label" for="isSystem">
                                {{ __('system_type') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function deleteActionType(id) {
    if (confirm('{{ __("are_you_sure_delete_action_type") }}')) {
        fetch(`/ai/action-types/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }
}

// Filtrage en temps réel
document.getElementById('searchInput').addEventListener('input', function(e) {
    // Implémenter la logique de filtrage
});

document.getElementById('categoryFilter').addEventListener('change', function(e) {
    // Implémenter la logique de filtrage
});

document.getElementById('statusFilter').addEventListener('change', function(e) {
    // Implémenter la logique de filtrage
});
</script>
@endpush

@push('styles')
<style>
.table th {
    font-weight: 500;
    font-size: 0.875rem;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.card {
    border: none;
    border-radius: 0.5rem;
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,.05);
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
@endpush
@endsection
