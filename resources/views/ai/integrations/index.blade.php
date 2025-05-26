@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('ai_integrations') }}</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="window.location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> {{ __('refresh') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createIntegrationModal">
                            <i class="bi bi-plus-lg"></i> {{ __('create_integration') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="searchInput" placeholder="{{ __('search_integrations') }}">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="moduleFilter">
                                <option value="">{{ __('all_modules') }}</option>
                                <option value="records">{{ __('records') }}</option>
                                <option value="slip">{{ __('slip') }}</option>
                                <option value="communication">{{ __('communication') }}</option>
                                <option value="mail">{{ __('mail') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">{{ __('all_statuses') }}</option>
                                <option value="1">{{ __('active') }}</option>
                                <option value="0">{{ __('inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="hookTypeFilter">
                                <option value="">{{ __('all_hook_types') }}</option>
                                <option value="before">{{ __('before') }}</option>
                                <option value="after">{{ __('after') }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tableau des intégrations -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ __('module') }}</th>
                                    <th>{{ __('event') }}</th>
                                    <th>{{ __('hook_type') }}</th>
                                    <th>{{ __('action_type') }}</th>
                                    <th>{{ __('prompt_template') }}</th>
                                    <th>{{ __('status') }}</th>
                                    <th>{{ __('created_at') }}</th>
                                    <th>{{ __('actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($integrations as $integration)
                                <tr>
                                    <td>{{ $integration->id }}</td>
                                    <td>{{ $integration->module_name }}</td>
                                    <td>{{ $integration->event_name }}</td>
                                    <td>
                                        <span class="badge bg-{{ $integration->hook_type === 'before' ? 'info' : 'success' }}">
                                            {{ $integration->hook_type }}
                                        </span>
                                    </td>
                                    <td>{{ $integration->actionType->display_name ?? '-' }}</td>
                                    <td>{{ $integration->promptTemplate->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $integration->is_active ? 'success' : 'danger' }}">
                                            {{ $integration->is_active ? __('active') : __('inactive') }}
                                        </span>
                                    </td>
                                    <td>{{ $integration->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('ai.integrations.show', $integration) }}" class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('ai.integrations.edit', $integration) }}" class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteIntegration({{ $integration->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                            {{ __('no_integrations_found') }}
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $integrations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de création -->
<div class="modal fade" id="createIntegrationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('ai.integrations.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('create_integration') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('module_name') }}</label>
                            <select name="module_name" class="form-select" required>
                                <option value="">{{ __('select_module') }}</option>
                                <option value="records">{{ __('records') }}</option>
                                <option value="slip">{{ __('slip') }}</option>
                                <option value="communication">{{ __('communication') }}</option>
                                <option value="mail">{{ __('mail') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('event_name') }}</label>
                            <input type="text" name="event_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('hook_type') }}</label>
                            <select name="hook_type" class="form-select" required>
                                <option value="before">{{ __('before') }}</option>
                                <option value="after">{{ __('after') }}</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('action_type') }}</label>
                            <select name="action_type_id" class="form-select" required>
                                <option value="">{{ __('select_action_type') }}</option>
                                @foreach($actionTypes as $actionType)
                                    <option value="{{ $actionType->id }}">{{ $actionType->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('prompt_template') }}</label>
                            <select name="ai_prompt_template_id" class="form-select">
                                <option value="">{{ __('select_prompt_template') }}</option>
                                @foreach($promptTemplates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('status') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" checked>
                                <label class="form-check-label">{{ __('active') }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('description') }}</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('configuration') }}</label>
                        <textarea name="configuration" class="form-control" rows="3" placeholder="{{ __('json_configuration') }}"></textarea>
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

@endsection

@push('scripts')
<script>
function deleteIntegration(id) {
    if (confirm('{{ __("confirm_delete_integration") }}')) {
        fetch(`/ai/integrations/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    }
}

// Filtrage en temps réel
document.getElementById('searchInput').addEventListener('input', filterTable);
document.getElementById('moduleFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);
document.getElementById('hookTypeFilter').addEventListener('change', filterTable);

function filterTable() {
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const moduleFilter = document.getElementById('moduleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const hookTypeFilter = document.getElementById('hookTypeFilter').value;

    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const module = row.cells[1].textContent.toLowerCase();
        const status = row.cells[6].textContent.includes('active') ? '1' : '0';
        const hookType = row.cells[3].textContent.trim();

        const matchesSearch = row.textContent.toLowerCase().includes(searchText);
        const matchesModule = !moduleFilter || module === moduleFilter.toLowerCase();
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesHookType = !hookTypeFilter || hookType === hookTypeFilter;

        row.style.display = matchesSearch && matchesModule && matchesStatus && matchesHookType ? '' : 'none';
    });
}
</script>
@endpush

@push('styles')
<style>
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

.form-switch .form-check-input {
    width: 2.5em;
    height: 1.25em;
}

.modal-lg {
    max-width: 800px;
}

textarea[name="configuration"] {
    font-family: monospace;
}
</style>
@endpush
