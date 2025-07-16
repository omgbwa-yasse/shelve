@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistiques détaillées du thésaurus</h3>
                    <div class="card-tools">
                        <a href="{{ route('thesaurus.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistiques générales -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h4>Statistiques générales</h4>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['schemes'] }}</h5>
                                    <p class="card-text">Schémas</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['concepts'] }}</h5>
                                    <p class="card-text">Concepts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-info">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['labels'] }}</h5>
                                    <p class="card-text">Labels</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['relations'] }}</h5>
                                    <p class="card-text">Relations</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card text-white bg-secondary">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['notes'] }}</h5>
                                    <p class="card-text">Notes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-dark">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['organizations'] }}</h5>
                                    <p class="card-text">Organisations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-danger">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['records_with_concepts'] }}</h5>
                                    <p class="card-text">Records liés</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card text-white bg-warning">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $stats['total_record_concept_relations'] }}</h5>
                                    <p class="card-text">Relations R-C</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Statistiques par schéma -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Statistiques par schéma</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Schéma</th>
                                                    <th>Concepts</th>
                                                    <th>Top Concepts</th>
                                                    <th>Taux</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($schemeStats as $scheme)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $scheme->identifier }}</strong><br>
                                                            <small class="text-muted">{{ $scheme->title }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-primary">{{ $scheme->concepts_count }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-success">{{ $scheme->top_concepts_count }}</span>
                                                        </td>
                                                        <td>
                                                            @if($scheme->concepts_count > 0)
                                                                {{ number_format(($scheme->top_concepts_count / $scheme->concepts_count) * 100, 1) }}%
                                                            @else
                                                                N/A
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques par type de relation -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Relations par type</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Type de relation</th>
                                                    <th>Nombre</th>
                                                    <th>Pourcentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalRelations = $relationStats->sum('count');
                                                @endphp
                                                @foreach($relationStats as $relation)
                                                    <tr>
                                                        <td>
                                                            @switch($relation->relation_type)
                                                                @case('broader')
                                                                    <span class="badge badge-primary">Broader</span>
                                                                    @break
                                                                @case('narrower')
                                                                    <span class="badge badge-success">Narrower</span>
                                                                    @break
                                                                @case('related')
                                                                    <span class="badge badge-info">Related</span>
                                                                    @break
                                                                @case('exactMatch')
                                                                    <span class="badge badge-warning">Exact Match</span>
                                                                    @break
                                                                @case('closeMatch')
                                                                    <span class="badge badge-secondary">Close Match</span>
                                                                    @break
                                                                @default
                                                                    <span class="badge badge-dark">{{ $relation->relation_type }}</span>
                                                            @endswitch
                                                        </td>
                                                        <td>{{ $relation->count }}</td>
                                                        <td>
                                                            @if($totalRelations > 0)
                                                                {{ number_format(($relation->count / $totalRelations) * 100, 1) }}%
                                                            @else
                                                                N/A
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Statistiques par type de label -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Labels par type</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Type de label</th>
                                                    <th>Nombre</th>
                                                    <th>Pourcentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalLabels = $labelStats->sum('count');
                                                @endphp
                                                @foreach($labelStats as $label)
                                                    <tr>
                                                        <td>
                                                            @switch($label->label_type)
                                                                @case('prefLabel')
                                                                    <span class="badge badge-primary">Préféré</span>
                                                                    @break
                                                                @case('altLabel')
                                                                    <span class="badge badge-success">Alternatif</span>
                                                                    @break
                                                                @case('hiddenLabel')
                                                                    <span class="badge badge-secondary">Caché</span>
                                                                    @break
                                                                @default
                                                                    <span class="badge badge-dark">{{ $label->label_type }}</span>
                                                            @endswitch
                                                        </td>
                                                        <td>{{ $label->count }}</td>
                                                        <td>
                                                            @if($totalLabels > 0)
                                                                {{ number_format(($label->count / $totalLabels) * 100, 1) }}%
                                                            @else
                                                                N/A
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top 10 des records les plus associés -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Records les plus liés</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Record</th>
                                                    <th>Concepts liés</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topRecords as $record)
                                                    <tr>
                                                        <td>
                                                            <a href="{{ route('records.show', $record->id) }}">
                                                                {{ $record->name }}
                                                            </a><br>
                                                            <small class="text-muted">{{ $record->code }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-primary">{{ $record->thesaurus_concepts_count }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <!-- Top 10 des concepts les plus utilisés -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Concepts les plus utilisés</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Concept</th>
                                                    <th>Schéma</th>
                                                    <th>URI</th>
                                                    <th>Records liés</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topConcepts as $concept)
                                                    <tr>
                                                        <td>
                                                            @php
                                                                $prefLabel = $concept->labels->where('label_type', 'prefLabel')->first();
                                                            @endphp
                                                            <strong>{{ $prefLabel ? $prefLabel->literal_form : 'Sans label' }}</strong><br>
                                                            @if($concept->notation)
                                                                <small class="text-muted">{{ $concept->notation }}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($concept->scheme)
                                                                <span class="badge badge-info">{{ $concept->scheme->identifier }}</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">{{ Str::limit($concept->uri, 50) }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-primary">{{ $concept->records_count }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
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
