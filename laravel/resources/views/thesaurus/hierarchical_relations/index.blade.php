@extends('layouts.app')

@section('title', 'Relations hiérarchiques')

@section('content')
<div class="container-fluid">
    <h1>Relations hiérarchiques pour "{{ $term->preferred_label }}"</h1>

    <div class="mb-3">
        <a href="{{ route('thesaurus.show', $term->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour au terme
        </a>
        <a href="{{ route('thesaurus.hierarchical_relations.broader.create', $term->id) }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Ajouter un terme générique (TG)
        </a>
        <a href="{{ route('thesaurus.hierarchical_relations.narrower.create', $term->id) }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Ajouter un terme spécifique (TS)
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Termes génériques (TG)</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @forelse($broaderTerms as $broaderTerm)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('thesaurus.show', $broaderTerm->id) }}">{{ $broaderTerm->preferred_label }}</a>
                                    <span class="badge bg-info">
                                        @switch($broaderTerm->pivot->relation_type)
                                            @case('generic')
                                                TG - Générique
                                                @break
                                            @case('partitive')
                                                TGP - Partitif
                                                @break
                                            @case('instance')
                                                TGI - Instance
                                                @break
                                            @default
                                                {{ $broaderTerm->pivot->relation_type }}
                                        @endswitch
                                    </span>
                                </div>
                                <form action="{{ route('thesaurus.hierarchical_relations.destroy', [$term->id, 'broader', $broaderTerm->id]) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette relation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aucun terme générique</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Termes spécifiques (TS)</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @forelse($narrowerTerms as $narrowerTerm)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('thesaurus.show', $narrowerTerm->id) }}">{{ $narrowerTerm->preferred_label }}</a>
                                    <span class="badge bg-info">
                                        @switch($narrowerTerm->pivot->relation_type)
                                            @case('generic')
                                                TS - Spécifique
                                                @break
                                            @case('partitive')
                                                TSP - Partitif
                                                @break
                                            @case('instance')
                                                TSI - Instance
                                                @break
                                            @default
                                                {{ $narrowerTerm->pivot->relation_type }}
                                        @endswitch
                                    </span>
                                </div>
                                <form action="{{ route('thesaurus.hierarchical_relations.destroy', [$term->id, 'narrower', $narrowerTerm->id]) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette relation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">Aucun terme spécifique</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
