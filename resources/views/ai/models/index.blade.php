@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('ai_models') }}</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="refreshModelsBtn">
                            <i class="bi bi-arrow-clockwise"></i> {{ __('refresh') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success me-2" id="syncOllamaBtn">
                            <i class="bi bi-cloud-download"></i> {{ __('sync_ollama_models') }}
                        </button>
                        <a href="{{ route('ai.models.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> {{ __('add_model') }}
                        </a>
                    </div>
                </div>
                <div id="ajax-alert" class="alert d-none mt-2" role="alert"></div>
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
                                            <span class="badge bg-{{ $model->status === 'active' ?
                                                'success' : ($model->status === 'training' ? 'warning' : 'secondary') }}">
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
                                                <a href="{{ route('ai.models.show.by.name', ['name' => $model->name]) }}"
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
    form.action = `{{ url('/ai/models') }}/${id}`;
    modal.show();
}

function trainModel(id) {
    const modal = new bootstrap.Modal(document.getElementById('trainModal'));
    const form = document.getElementById('trainForm');
    form.action = `{{ url('/ai/models') }}/${id}/train`;
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

    // Définir les URLs pour les actions
    const baseRoute = "{{ url('/ai/models') }}";
    // Générer des URLs correctes pour les modèles
    function getModelShowUrl(id, name) {
        // Si le nom est fourni, utiliser la route par nom, sinon utiliser l'ID
        return name ? `${baseRoute}/name/${encodeURIComponent(name)}` : `${baseRoute}/${id}`;
    }
    function getModelEditUrl(id) {
        return `${baseRoute}/${id}/edit`;
    }

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

    // AJAX pour rafraîchir les modèles
    const refreshModelsBtn = document.getElementById('refreshModelsBtn');
    const syncOllamaBtn = document.getElementById('syncOllamaBtn');
    const ajaxAlert = document.getElementById('ajax-alert');

    function showAlert(message, type) {
        ajaxAlert.innerText = message;
        ajaxAlert.className = `alert alert-${type} mt-2`;

        // Masquer l'alerte après 4 secondes
        setTimeout(() => {
            ajaxAlert.className = 'alert d-none mt-2';
        }, 4000);
    }

    // Actualiser les modèles depuis la base de données
    refreshModelsBtn.addEventListener('click', async () => {
        refreshModelsBtn.disabled = true;
        refreshModelsBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Chargement...';

        try {
            const response = await fetch('{{ route('ai.models.index') }}?ajax=1', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des modèles');
            }

            const data = await response.json();
            updateModelsTable(data.models);
            showAlert('Liste des modèles actualisée avec succès', 'success');
        } catch (error) {
            console.error('Erreur AJAX:', error);
            showAlert('Erreur lors de l\'actualisation des modèles: ' + error.message, 'danger');
        } finally {
            refreshModelsBtn.disabled = false;
            refreshModelsBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> {{ __('refresh') }}';
        }
    });

    // Synchroniser avec les modèles Ollama
    syncOllamaBtn.addEventListener('click', async () => {
        if (!confirm('{{ __("confirm_ollama_sync") }}')) return;

        syncOllamaBtn.disabled = true;
        syncOllamaBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __("synchronizing") }}...';

        try {
            // Vérifier d'abord la connectivité avec Ollama
            const healthResponse = await fetch('/api/ai/ollama/health', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const healthData = await healthResponse.json();

            if (healthData.status !== 'healthy') {
                throw new Error('{{ __("ollama_not_available") }}: ' + healthData.message);
            }

            // Lancer la synchronisation
            const response = await fetch('/api/ai/ollama/models/sync', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || '{{ __("sync_error") }}');
            }

            const data = await response.json();

            if (data.success) {
                showAlert(`${data.synced_count} {{ __("models_synced_successfully") }}`, 'success');
                // Actualiser la liste après la synchronisation
                refreshModelsBtn.click();
            } else {
                throw new Error(data.message || '{{ __("unknown_error") }}');
            }
        } catch (error) {
            console.error('Erreur AJAX:', error);
            showAlert('{{ __("sync_error") }}: ' + error.message, 'danger');
        } finally {
            syncOllamaBtn.disabled = false;
            syncOllamaBtn.innerHTML = '<i class="bi bi-cloud-download"></i> {{ __("sync_ollama_models") }}';
        }
    });

    // Mettre à jour le tableau des modèles avec les nouvelles données
    function updateModelsTable(models) {
        const tbody = document.querySelector('table tbody');
        tbody.innerHTML = '';

        if (models.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="8" class="text-center">{{ __('no_models_found') }}</td>`;
            tbody.appendChild(tr);
            return;
        }

        models.forEach(model => {
            const tr = document.createElement('tr');

            // Définir le statut et l'icône
            const statusClass = model.status === 'active' ? 'success' :
                               (model.status === 'training' ? 'warning' : 'secondary');

            const iconClass = model.type === 'text' ? 'chat-dots' :
                             (model.type === 'image' ? 'image' :
                             (model.type === 'code' ? 'code-square' : 'cpu'));

            // Calculer les métriques de performance
            let performanceHtml = '-';
            if (model.performance_metrics) {
                const metrics = JSON.parse(model.performance_metrics);
                const accuracy = metrics.accuracy || 0;
                performanceHtml = `
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-2" style="height: 5px;">
                            <div class="progress-bar bg-success"
                                 role="progressbar"
                                 style="width: ${accuracy}%"
                                 aria-valuenow="${accuracy}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <small class="text-muted">${accuracy.toFixed(1)}%</small>
                    </div>
                `;
            }

            // Formater la date
            let lastTrained = model.last_trained_at ? new Date(model.last_trained_at).toLocaleString() : '-';

            tr.innerHTML = `
                <td>${model.id}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-${iconClass} me-2 text-primary"></i>
                        ${model.name}
                    </div>
                </td>
                <td>
                    <span class="badge bg-info">
                        ${model.type}_model
                    </span>
                </td>
                <td>${model.version}</td>
                <td>
                    <span class="badge bg-${statusClass}">
                        ${model.status}
                    </span>
                </td>
                <td>${performanceHtml}</td>
                <td>${lastTrained}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="${getModelShowUrl(model.id, model.name)}" class="btn btn-outline-primary" title="{{ __('view') }}">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="${getModelEditUrl(model.id)}" class="btn btn-outline-secondary" title="{{ __('edit') }}">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button onclick="deleteModel(${model.id})" class="btn btn-outline-danger" title="{{ __('delete') }}">
                            <i class="bi bi-trash"></i>
                        </button>
                        <button onclick="trainModel(${model.id})" class="btn btn-outline-warning" title="{{ __('train') }}">
                            <i class="bi bi-cpu"></i>
                        </button>
                    </div>
                </td>
            `;

            tbody.appendChild(tr);
        });

        // Réappliquer les filtres après mise à jour
        filterTable();
    }
});
</script>
@endpush
