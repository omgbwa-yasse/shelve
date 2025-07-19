@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Détail du schéma de thésaurus: {{ $scheme->title }}
                    </h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            <a href="{{ route('thesaurus.schemes.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                            <a href="{{ route('thesaurus.schemes.edit', $scheme->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Informations du schéma</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 30%">ID</th>
                                            <td>{{ $scheme->id }}</td>
                                        </tr>
                                        <tr>
                                            <th>Identifiant</th>
                                            <td><span class="badge badge-info">{{ $scheme->identifier }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Titre</th>
                                            <td>{{ $scheme->title }}</td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td>{{ $scheme->description ?: 'Non spécifié' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Langue</th>
                                            <td>{{ $scheme->language }}</td>
                                        </tr>
                                        <tr>
                                            <th>URI</th>
                                            <td>
                                                <a href="{{ $scheme->uri }}" target="_blank">
                                                    {{ $scheme->uri }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Date de création</th>
                                            <td>{{ $scheme->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Dernière mise à jour</th>
                                            <td>{{ $scheme->updated_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Namespace</h5>
                                </div>
                                <div class="card-body">
                                    @if ($scheme->namespace)
                                        <table class="table table-borderless">
                                            <tr>
                                                <th style="width: 30%">Préfixe</th>
                                                <td><span class="badge badge-primary">{{ $scheme->namespace->prefix }}</span></td>
                                            </tr>
                                            <tr>
                                                <th>URI</th>
                                                <td>
                                                    <a href="{{ $scheme->namespace->namespace_uri }}" target="_blank">
                                                        {{ $scheme->namespace->namespace_uri }}
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Description</th>
                                                <td>{{ $scheme->namespace->description ?: 'Non spécifié' }}</td>
                                            </tr>
                                        </table>
                                    @else
                                        <p class="text-muted">Aucun namespace défini pour ce schéma</p>
                                    @endif
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Statistiques</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 text-center">
                                            <div class="info-box bg-info">
                                                <span class="info-box-icon"><i class="fas fa-cubes"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Concepts</span>
                                                    <span class="info-box-number">{{ $scheme->concepts->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-center">
                                            <div class="info-box bg-success">
                                                <span class="info-box-icon"><i class="fas fa-tags"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Top Concepts</span>
                                                    <span class="info-box-number">{{ $scheme->topConcepts->count() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des concepts -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Concepts (10 derniers)</h5>
                        </div>
                        <div class="card-body">
                            @if ($scheme->concepts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>URI</th>
                                                <th>Labels</th>
                                                <th>Relations</th>
                                                <th>Créé le</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($scheme->concepts->sortByDesc('created_at')->take(10) as $concept)
                                                <tr>
                                                    <td>{{ $concept->id }}</td>
                                                    <td>{{ Str::limit($concept->uri, 30) }}</td>
                                                    <td>
                                                        @foreach ($concept->labels->take(2) as $label)
                                                            <span class="badge {{ $label->type == 'prefLabel' ? 'badge-primary' : 'badge-secondary' }}">
                                                                {{ $label->literal_form }}
                                                            </span>
                                                        @endforeach
                                                        @if ($concept->labels->count() > 2)
                                                            <small class="text-muted">+{{ $concept->labels->count() - 2 }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $concept->relations->count() }}</span>
                                                    </td>
                                                    <td>{{ $concept->created_at->format('d/m/Y') }}</td>
                                                    <td>
                                                        <a href="#" class="btn btn-xs btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if ($scheme->concepts->count() > 10)
                                    <div class="text-center mt-3">
                                        <a href="#" class="btn btn-outline-primary">
                                            Voir tous les concepts ({{ $scheme->concepts->count() }})
                                        </a>
                                    </div>
                                @endif
                            @else
                                <p class="text-muted">Aucun concept dans ce schéma</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
