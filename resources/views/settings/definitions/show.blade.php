@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Détails du paramètre</h5>
                    <div>
                        <a href="{{ route('settings.definitions.edit', $setting) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="{{ route('settings.definitions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Informations générales</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">Catégorie :</th>
                                    <td>{{ $setting->category->name ?? 'Sans catégorie' }}</td>
                                </tr>
                                <tr>
                                    <th>Nom :</th>
                                    <td><code>{{ $setting->name }}</code></td>
                                </tr>
                                <tr>
                                    <th>Type :</th>
                                    <td>
                                        <span class="badge bg-info">{{ $setting->type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Statut :</th>
                                    <td>
                                        @if($setting->is_system)
                                            <span class="badge bg-warning">Système</span>
                                        @else
                                            <span class="badge bg-success">Utilisateur</span>
                                </tr>
                                <tr>
                                    <th>Créé le :</th>
                                    <td>{{ $setting->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Modifié le :</th>
                                    <td>{{ $setting->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Valeurs</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">Valeur par défaut :</th>
                                    <td><code>{{ json_encode($setting->default_value) }}</code></td>
                                </tr>
                                <tr>
                                    <th>Valeur actuelle :</th>
                                    <td>
                                        @if($setting->hasCustomValue())
                                            <code class="text-success">{{ json_encode($setting->value) }}</code>
                                            <small class="text-muted d-block">Valeur personnalisée</small>
                                        @else
                                            <code class="text-muted">{{ json_encode($setting->default_value) }}</code>
                                            <small class="text-muted d-block">Valeur par défaut</small>
                                        @endif
                                    </td>
                                </tr>
                                @if($setting->user_id)
                                <tr>
                                    <th>Utilisateur :</th>
                                    <td>{{ $setting->user->name ?? 'N/A' }}</td>
                                </tr>
                                @endif
                                @if($setting->organisation_id)
                                <tr>
                                    <th>Organisation :</th>
                                    <td>{{ $setting->organisation->name ?? 'N/A' }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($setting->description)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">Description</h6>
                            <div class="bg-light p-3 rounded">
                                {{ $setting->description }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($setting->constraints)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">Contraintes</h6>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <pre class="mb-0">{{ json_encode($setting->constraints, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                    @endif
                        </div>
                    </div>
                    @endif

                    <!-- Actions pour personnaliser le paramètre -->
                    @if(!$setting->hasCustomValue())
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Personnaliser ce paramètre</h6>
                                    <p class="card-text text-muted">Ce paramètre utilise actuellement sa valeur par défaut. Vous pouvez le personnaliser pour votre compte.</p>
                                    <button type="button" class="btn btn-primary" onclick="showCustomizeModal()">
                                        <i class="fas fa-cog"></i> Personnaliser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <h6 class="card-title text-success">Paramètre personnalisé</h6>
                                    <p class="card-text">Ce paramètre a été personnalisé pour votre compte.</p>
                                    <button type="button" class="btn btn-warning" onclick="resetSetting()">
                                        <i class="fas fa-undo"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <form method="POST" action="{{ route('settings.definitions.destroy', $setting) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce paramètre ? Toutes les valeurs associées seront également supprimées.')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour personnaliser un paramètre -->
<div class="modal fade" id="customizeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Personnaliser le paramètre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="customizeForm">
                    @csrf
                    <div class="mb-3">
                        <label for="customValue" class="form-label">Nouvelle valeur</label>
                        <input type="text" class="form-control" id="customValue" name="value" required>
                        <div class="form-text">Type: {{ $setting->type }}</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="saveCustomValue()">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script>
function showCustomizeModal() {
    document.getElementById('customValue').value = '';
    new bootstrap.Modal(document.getElementById('customizeModal')).show();
}

function saveCustomValue() {
    const value = document.getElementById('customValue').value;
    const settingId = {{ $setting->id }};

    fetch(`/settings/definitions/${settingId}/set-value`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ value: value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Erreur: ' + (data.errors?.value?.[0] || data.error));
        } else {
            location.reload();
        }
    })
    .catch(error => {
        alert('Erreur lors de la sauvegarde');
        console.error(error);
    });
}

function resetSetting() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser ce paramètre à sa valeur par défaut ?')) {
        const settingId = {{ $setting->id }};

        fetch(`/settings/definitions/${settingId}/reset-value`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            } else {
                alert('Erreur lors de la réinitialisation');
            }
        })
        .catch(error => {
            alert('Erreur lors de la réinitialisation');
            console.error(error);
        });
    }
}
</script>
@endsection
