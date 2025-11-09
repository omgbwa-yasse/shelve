@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-collection"></i> {{ __('Détails de la collection') }}</h1>
        <div>
            <a href="{{ route('museum.collections.edit', $collection ?? 1) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> {{ __('Modifier') }}
            </a>
            <a href="{{ route('museum.collections.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> {{ __('Informations générales') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>{{ __('Nom de la collection') }}:</strong>
                            <p>{{ __('Collection exemple') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Code') }}:</strong>
                            <p>-</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>{{ __('Description') }}:</strong>
                        <p>{{ __('Description de la collection...') }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('Période') }}:</strong>
                            <p>-</p>
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('Origine géographique') }}:</strong>
                            <p>-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des artefacts de la collection -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> {{ __('Artefacts de la collection') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Nom') }}</th>
                                    <th>{{ __('Période') }}</th>
                                    <th>{{ __('État') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        {{ __('Aucun artefact dans cette collection') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques et actions -->
        <div class="col-md-4">
            <!-- Statistiques -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> {{ __('Statistiques') }}</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        <h2 class="text-primary">0</h2>
                        <p class="text-muted">{{ __('Pièces totales') }}</p>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <strong>{{ __('En exposition') }}:</strong>
                        <span class="float-end">0</span>
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('En prêt') }}:</strong>
                        <span class="float-end">0</span>
                    </div>
                    <div class="mb-2">
                        <strong>{{ __('En réserve') }}:</strong>
                        <span class="float-end">0</span>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <strong>{{ __('Valeur totale') }}:</strong>
                        <h4 class="text-success mb-0">0 €</h4>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> {{ __('Actions rapides') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> {{ __('Ajouter un artefact') }}
                        </a>
                        <a href="#" class="btn btn-secondary">
                            <i class="bi bi-file-earmark-pdf"></i> {{ __('Générer un rapport') }}
                        </a>
                        <a href="#" class="btn btn-info">
                            <i class="bi bi-printer"></i> {{ __('Imprimer') }}
                        </a>
                        <a href="#" class="btn btn-success">
                            <i class="bi bi-download"></i> {{ __('Exporter') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
