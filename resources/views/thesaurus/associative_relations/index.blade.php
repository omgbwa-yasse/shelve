@extends('layouts.app')

@section('title', 'Relations associatives')

@section('content')
<div class="container-fluid">
    <h1>Relations associatives pour "{{ $term->preferred_label }}"</h1>

    <div class="mb-3">
        <a href="{{ route('terms.show', $term->id) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour au terme
        </a>
        <a href="{{ route('terms.associative-relations.create', $term->id) }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Ajouter une relation associative
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Termes associés (TA)</h5>
        </div>
        <div class="card-body">
            <ul class="list-group">
                @forelse($associatedTerms as $associatedTerm)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('terms.show', $associatedTerm->id) }}">{{ $associatedTerm->preferred_label }}</a>
                            <span class="badge bg-info">
                                @switch($associatedTerm->pivot->relation_subtype)
                                    @case('cause_effect')
                                        Cause/Effet
                                        @break
                                    @case('whole_part')
                                        Tout/Partie
                                        @break
                                    @case('action_agent')
                                        Action/Agent
                                        @break
                                    @case('action_product')
                                        Action/Produit
                                        @break
                                    @case('action_object')
                                        Action/Objet
                                        @break
                                    @case('action_location')
                                        Action/Lieu
                                        @break
                                    @case('science_object')
                                        Science/Objet d'étude
                                        @break
                                    @case('object_property')
                                        Objet/Propriété
                                        @break
                                    @case('object_role')
                                        Objet/Rôle
                                        @break
                                    @case('raw_material_product')
                                        Matière première/Produit
                                        @break
                                    @case('process_neutralizer')
                                        Processus/Neutraliseur
                                        @break
                                    @case('object_origin')
                                        Objet/Origine
                                        @break
                                    @case('concept_measurement')
                                        Concept/Mesure
                                        @break
                                    @case('profession_person')
                                        Profession/Personne
                                        @break
                                    @default
                                        Association générale
                                @endswitch
                            </span>
                        </div>
                        <form action="{{ route('terms.associative-relations.destroy', [$term->id, $associatedTerm->id]) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette relation?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Aucun terme associé</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
