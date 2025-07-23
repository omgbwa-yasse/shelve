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
                                    <th>Clé :</th>
                                    <td><code>{{ $setting->key }}</code></td>
                                </tr>
                                <tr>
                                    <th>Libellé :</th>
                                    <td><strong>{{ $setting->label }}</strong></td>
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
                                        @if($setting->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-secondary">Inactif</span>
                                        @endif

                                        @if($setting->is_required)
                                            <span class="badge bg-warning">Obligatoire</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Horodatage</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">Créé le :</th>
                                    <td>{{ $setting->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Modifié le :</th>
                                    <td>{{ $setting->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
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

                    @if($setting->default_value)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">Valeur par défaut</h6>
                            <div class="bg-secondary bg-opacity-10 p-3 rounded">
                                <pre class="mb-0">{{ $setting->default_value }}</pre>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($setting->constraints)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">Contraintes</h6>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <pre class="mb-0">{{ $setting->constraints }}</pre>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Valeurs associées -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="text-muted mb-3">Valeurs définies ({{ $setting->values->count() }})</h6>
                                <a href="{{ route('settings.values.create', ['setting_id' => $setting->id]) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus"></i> Ajouter une valeur
                                </a>
                            </div>

                            @if($setting->values->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Utilisateur</th>
                                                <th>Organisation</th>
                                                <th>Valeur</th>
                                                <th>Créée le</th>
                                                <th width="100">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($setting->values as $value)
                                            <tr>
                                                <td>{{ $value->user->name ?? 'Non spécifié' }}</td>
                                                <td>{{ $value->organisation->name ?? 'Non spécifiée' }}</td>
                                                <td>
                                                    <code class="text-truncate d-block" style="max-width: 200px;">
                                                        {{ Str::limit(is_string($value->value) ? $value->value : json_encode($value->value), 50) }}
                                                    </code>
                                                </td>
                                                <td>{{ $value->created_at->format('d/m/Y') }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('settings.values.show', $value) }}" class="btn btn-outline-primary btn-sm" title="Voir">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('settings.values.edit', $value) }}" class="btn btn-outline-warning btn-sm" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Aucune valeur définie pour ce paramètre</p>
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
@endsection
