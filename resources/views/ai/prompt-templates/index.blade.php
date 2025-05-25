@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-file-text text-primary me-2"></i>
                            {{ __('ai_prompt_templates') }}
                        </h5>
                        <div>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="refreshTemplates()">
                                <i class="bi bi-arrow-clockwise me-1"></i> {{ __('refresh') }}
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('create_template') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="searchInput" placeholder="{{ __('search_templates') }}">
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
                        <div class="col-md-2">
                            <select class="form-select" id="sortFilter">
                                <option value="newest">{{ __('newest_first') }}</option>
                                <option value="oldest">{{ __('oldest_first') }}</option>
                                <option value="name_asc">{{ __('name_asc') }}</option>
                                <option value="name_desc">{{ __('name_desc') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Liste des templates -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('id') }}</th>
                                    <th>{{ __('name') }}</th>
                                    <th>{{ __('category') }}</th>
                                    <th>{{ __('action_type') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('created_at') }}</th>
                                    <th>{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promptTemplates as $template)
                                <tr>
                                    <td>{{ $template->id }}</td>
                                    <td>
                                        <div class="fw-medium">{{ $template->name }}</div>
                                        <div class="small text-muted">{{ Str::limit($template->description, 50) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{
                                            $template->category === 'text' ? 'primary' :
                                            ($template->category === 'image' ? 'success' :
                                            ($template->category === 'code' ? 'warning' : 'info'))
                                        }}">
                                            {{ $template->category }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('ai.action-types.show', $template->actionType) }}" class="text-decoration-none">
                                            {{ $template->actionType->display_name }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $template->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ $template->status }}
                                        </span>
                                    </td>
                                    <td>{{ $template->created_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('ai.prompt-templates.show', $template) }}"
                                               class="btn btn-outline-primary"
                                               title="{{ __('view') }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('ai.prompt-templates.edit', $template) }}"
                                               class="btn btn-outline-secondary"
                                               title="{{ __('edit') }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if(!$template->is_system)
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    onclick="deleteTemplate({{ $template->id }})"
                                                    title="{{ __('delete') }}">
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
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            {{ __('no_templates_found') }}
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-end mt-4">
                        {{ $promptTemplates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de création -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('ai.prompt-templates.store') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle text-primary me-2"></i>
                        {{ __('create_template') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">{{ __('name') }}</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">{{ __('action_type') }}</label>
                            <select class="form-select @error('action_type_id') is-invalid @enderror"
                                    name="action_type_id" required>
                                <option value="">{{ __('select_action_type') }}</option>
                                @foreach($actionTypes as $actionType)
                                <option value="{{ $actionType->id }}" {{ old('action_type_id') == $actionType->id ? 'selected' : '' }}>
                                    {{ $actionType->display_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('action_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">{{ __('category') }}</label>
                            <select class="form-select @error('category') is-invalid @enderror"
                                    name="category" required>
                                <option value="">{{ __('select_category') }}</option>
                                <option value="text" {{ old('category') == 'text' ? 'selected' : '' }}>
                                    {{ __('text') }}
                                </option>
                                <option value="image" {{ old('category') == 'image' ? 'selected' : '' }}>
                                    {{ __('image') }}
                                </option>
                                <option value="code" {{ old('category') == 'code' ? 'selected' : '' }}>
                                    {{ __('code') }}
                                </option>
                                <option value="data" {{ old('category') == 'data' ? 'selected' : '' }}>
                                    {{ __('data') }}
                                </option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">{{ __('status') }}</label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    name="status" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                    {{ __('active') }}
                                </option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                    {{ __('inactive') }}
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      name="description" rows="2">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label required">{{ __('prompt_template') }}</label>
                            <textarea class="form-control @error('template') is-invalid @enderror"
                                      name="template" rows="6" required
                                      placeholder="{{ __('prompt_template_placeholder') }}">{{ old('template') }}</textarea>
                            @error('template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('prompt_template_help') }}</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('variables') }}</label>
                            <textarea class="form-control @error('variables') is-invalid @enderror"
                                      name="variables" rows="3"
                                      placeholder="{{ __('json_format') }}">{{ old('variables') }}</textarea>
                            @error('variables')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">{{ __('variables_help') }}</div>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input @error('is_system') is-invalid @enderror"
                                       name="is_system" id="isSystem" value="1" {{ old('is_system') ? 'checked' : '' }}>
                                <label class="form-check-label" for="isSystem">
                                    {{ __('system_template') }}
                                </label>
                                @error('is_system')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        {{ __('cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> {{ __('create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.required:after {
    content: " *";
    color: #dc3545;
}

.card {
    border-radius: 0.5rem;
}

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

textarea[name="variables"] {
    font-family: monospace;
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
// Validation des formulaires Bootstrap
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Validation JSON en temps réel pour les variables
document.querySelector('textarea[name="variables"]').addEventListener('input', function() {
    try {
        if (this.value) {
            JSON.parse(this.value);
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid', 'is-invalid');
        }
    } catch (e) {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
    }
});

// Filtrage en temps réel
document.getElementById('searchInput').addEventListener('input', filterTemplates);
document.getElementById('categoryFilter').addEventListener('change', filterTemplates);
document.getElementById('statusFilter').addEventListener('change', filterTemplates);
document.getElementById('sortFilter').addEventListener('change', filterTemplates);

function filterTemplates() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const status = document.getElementById('statusFilter').value;
    const sort = document.getElementById('sortFilter').value;

    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const rowCategory = row.querySelector('td:nth-child(3) .badge').textContent;
        const rowStatus = row.querySelector('td:nth-child(5) .badge').textContent;

        const matchesSearch = name.includes(search);
        const matchesCategory = !category || rowCategory === category;
        const matchesStatus = !status || rowStatus === status;

        row.style.display = matchesSearch && matchesCategory && matchesStatus ? '' : 'none';
    });
}

function refreshTemplates() {
    window.location.reload();
}

function deleteTemplate(id) {
    if (confirm('{{ __("confirm_delete_template") }}')) {
        fetch(`/ai/prompt-templates/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                alert('{{ __("error_deleting_template") }}');
            }
        });
    }
}
</script>
@endpush
@endsection
