@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Détails de la valeur</h5>
                    <div>
                        <a href="{{ route('settings.values.edit', $settingValue) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="{{ route('settings.values.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Informations du paramètre</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">Catégorie :</th>
                                    <td>{{ $settingValue->setting->category->name ?? 'Sans catégorie' }}</td>
                                </tr>
                                <tr>
                                    <th>Paramètre :</th>
                                    <td>
                                        <strong>{{ $settingValue->setting->label }}</strong>
                                        <br><small class="text-muted">{{ $settingValue->setting->key }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Type :</th>
                                    <td>
                                        <span class="badge bg-info">{{ $settingValue->setting->type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Description :</th>
                                    <td>{{ $settingValue->setting->description ?? 'Aucune description' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted">Informations de la valeur</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th style="width: 30%">Utilisateur :</th>
                                    <td>{{ $settingValue->user->name ?? 'Non spécifié' }}</td>
                                </tr>
                                <tr>
                                    <th>Organisation :</th>
                                    <td>{{ $settingValue->organisation->name ?? 'Non spécifiée' }}</td>
                                </tr>
                                <tr>
                                    <th>Valeur :</th>
                                    <td>
                                        <div class="bg-light p-2 rounded">
                                            <pre class="mb-0">{{ $settingValue->value }}</pre>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Créée le :</th>
                                    <td>{{ $settingValue->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Modifiée le :</th>
                                    <td>{{ $settingValue->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($settingValue->setting->default_value)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">Valeur par défaut du paramètre</h6>
                            <div class="bg-secondary bg-opacity-10 p-2 rounded">
                                <pre class="mb-0">{{ $settingValue->setting->default_value }}</pre>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($settingValue->setting->constraints)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="text-muted">Contraintes du paramètre</h6>
                            <div class="bg-warning bg-opacity-10 p-2 rounded">
                                <pre class="mb-0">{{ $settingValue->setting->constraints }}</pre>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <form method="POST" action="{{ route('settings.values.destroy', $settingValue) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette valeur ?')">
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
