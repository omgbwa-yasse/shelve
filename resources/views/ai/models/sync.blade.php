@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('sync_ollama_models') }}</h5>
                    <div>
                        <a href="{{ route('ai.models.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> {{ __('back_to_models') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="sync-status" class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <span class="badge p-2 {{ $isConnected ? 'bg-success' : 'bg-danger' }}">
                                    <i class="bi {{ $isConnected ? 'bi-check-circle' : 'bi-x-circle' }}"></i>
                                    {{ $isConnected ? __('connected_to_ollama') : __('not_connected_to_ollama') }}
                                </span>
                            </div>
                            <button id="check-connection" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-arrow-repeat"></i> {{ __('check_connection') }}
                            </button>
                        </div>

                        @if(!$isConnected)
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                {{ __('ollama_not_connected_instructions') }}
                                <ul class="mt-2">
                                    <li>{{ __('ensure_ollama_running') }}</li>
                                    <li>{{ __('check_ollama_url_config') }} <code>{{ config('ollama.base_url') }}</code></li>
                                    <li>{{ __('check_network_connection') }}</li>
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <!-- Modèles Ollama disponibles -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">{{ __('available_ollama_models') }}</h6>
                                </div>
                                <div class="card-body">
                                    @if($isConnected && count($ollamaModels) > 0)
                                        <ul class="list-group">
                                            @foreach($ollamaModels as $model)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>{{ $model['name'] }}</strong>
                                                        @if(isset($model['details']['parameter_size']))
                                                            <span class="badge bg-info ms-2">{{ $model['details']['parameter_size'] }}</span>
                                                        @endif
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ __('size') }}: {{ round($model['size'] / (1024*1024), 2) }} MB
                                                        </small>
                                                    </div>

                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="sync-{{ $model['name'] }}"
                                                               value="{{ $model['name'] }}"
                                                               {{ $models->contains('name', $model['name']) ? 'checked disabled' : '' }}>
                                                        <label class="form-check-label" for="sync-{{ $model['name'] }}">
                                                            {{ $models->contains('name', $model['name']) ? __('already_synced') : __('sync') }}
                                                        </label>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @elseif(!$isConnected)
                                        <div class="text-center py-5">
                                            <i class="bi bi-cloud-slash text-muted" style="font-size: 3rem;"></i>
                                            <p class="mt-3">{{ __('cannot_fetch_ollama_models') }}</p>
                                        </div>
                                    @else
                                        <div class="text-center py-5">
                                            <i class="bi bi-emoji-frown text-muted" style="font-size: 3rem;"></i>
                                            <p class="mt-3">{{ __('no_models_available_in_ollama') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer">
                                    <button id="sync-selected" class="btn btn-primary" {{ !$isConnected ? 'disabled' : '' }}>
                                        <i class="bi bi-cloud-download"></i> {{ __('sync_selected_models') }}
                                    </button>
                                    <button id="sync-all" class="btn btn-outline-primary" {{ !$isConnected ? 'disabled' : '' }}>
                                        <i class="bi bi-cloud-download-fill"></i> {{ __('sync_all_models') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Modèles synchronisés dans la base -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">{{ __('synced_models_in_database') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('name') }}</th>
                                                    <th>{{ __('version') }}</th>
                                                    <th>{{ __('status') }}</th>
                                                    <th>{{ __('synced_at') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($models as $model)
                                                    <tr>
                                                        <td>{{ $model->name }}</td>
                                                        <td>{{ $model->version }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $model->is_active ? 'success' : 'secondary' }}">
                                                                {{ $model->is_active ? __('active') : __('inactive') }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $model->updated_at->format('Y-m-d H:i:s') }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">{{ __('no_synced_models') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkConnectionBtn = document.getElementById('check-connection');
    const syncSelectedBtn = document.getElementById('sync-selected');
    const syncAllBtn = document.getElementById('sync-all');
    const syncStatus = document.getElementById('sync-status');

    // Vérifier la connexion à Ollama
    checkConnectionBtn.addEventListener('click', async function() {
        try {
            checkConnectionBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Vérification...';
            checkConnectionBtn.disabled = true;

            const response = await fetch('/api/ai/ollama/health-check', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            let statusHtml = '';
            if (data.status === 'healthy') {
                statusHtml = `
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <span class="badge p-2 bg-success">
                                <i class="bi bi-check-circle"></i> {{ __('connected_to_ollama') }}
                            </span>
                        </div>
                        <button id="check-connection" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-repeat"></i> {{ __('check_connection') }}
                        </button>
                    </div>
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle"></i>
                        {{ __('ollama_connection_successful') }}
                    </div>
                `;

                // Réactiver les boutons de synchronisation
                syncSelectedBtn.disabled = false;
                syncAllBtn.disabled = false;

                // Recharger la page pour afficher les modèles disponibles
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                statusHtml = `
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <span class="badge p-2 bg-danger">
                                <i class="bi bi-x-circle"></i> {{ __('not_connected_to_ollama') }}
                            </span>
                        </div>
                        <button id="check-connection" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-repeat"></i> {{ __('check_connection') }}
                        </button>
                    </div>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        {{ __('ollama_connection_failed') }}: ${data.message || 'Erreur inconnue'}
                    </div>
                `;

                // Désactiver les boutons de synchronisation
                syncSelectedBtn.disabled = true;
                syncAllBtn.disabled = true;
            }

            syncStatus.innerHTML = statusHtml;

            // Réattacher l'événement au nouveau bouton
            document.getElementById('check-connection').addEventListener('click', arguments.callee);

        } catch (error) {
            console.error('Erreur lors de la vérification de la connexion:', error);

            syncStatus.innerHTML = `
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <span class="badge p-2 bg-danger">
                            <i class="bi bi-x-circle"></i> {{ __('not_connected_to_ollama') }}
                        </span>
                    </div>
                    <button id="check-connection" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-arrow-repeat"></i> {{ __('check_connection') }}
                    </button>
                </div>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ __('ollama_connection_error') }}: ${error.message}
                </div>
            `;

            // Réattacher l'événement au nouveau bouton
            document.getElementById('check-connection').addEventListener('click', arguments.callee);

            // Désactiver les boutons de synchronisation
            syncSelectedBtn.disabled = true;
            syncAllBtn.disabled = true;
        }
    });

    // Synchroniser les modèles sélectionnés
    syncSelectedBtn.addEventListener('click', async function() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked:not(:disabled)');
        const models = Array.from(checkboxes).map(cb => cb.value);

        if (models.length === 0) {
            alert('{{ __("please_select_models_to_sync") }}');
            return;
        }

        if (!confirm(`{{ __("confirm_sync_models") }} (${models.length})?`)) return;

        syncSelectedBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __("syncing") }}...';
        syncSelectedBtn.disabled = true;

        try {
            const response = await fetch('/api/ai/ollama/models/sync', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ models: models })
            });

            const data = await response.json();

            if (data.success) {
                alert(`{{ __("sync_successful") }}: ${data.synced_count} {{ __("models_synced") }}`);
                window.location.reload();
            } else {
                throw new Error(data.message || '{{ __("unknown_error") }}');
            }
        } catch (error) {
            console.error('{{ __("sync_error") }}:', error);
            alert(`{{ __("sync_failed") }}: ${error.message}`);
        } finally {
            syncSelectedBtn.innerHTML = '<i class="bi bi-cloud-download"></i> {{ __("sync_selected_models") }}';
            syncSelectedBtn.disabled = false;
        }
    });

    // Synchroniser tous les modèles
    syncAllBtn.addEventListener('click', async function() {
        if (!confirm('{{ __("confirm_sync_all_models") }}')) return;

        syncAllBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __("syncing") }}...';
        syncAllBtn.disabled = true;

        try {
            const response = await fetch('/api/ai/ollama/models/sync', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert(`{{ __("sync_successful") }}: ${data.synced_count} {{ __("models_synced") }}`);
                window.location.reload();
            } else {
                throw new Error(data.message || '{{ __("unknown_error") }}');
            }
        } catch (error) {
            console.error('{{ __("sync_error") }}:', error);
            alert(`{{ __("sync_failed") }}: ${error.message}`);
        } finally {
            syncAllBtn.innerHTML = '<i class="bi bi-cloud-download-fill"></i> {{ __("sync_all_models") }}';
            syncAllBtn.disabled = false;
        }
    });
});
</script>
@endpush
