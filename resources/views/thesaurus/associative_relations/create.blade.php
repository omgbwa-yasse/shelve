@extends('layouts.app')

@section('title', 'Ajouter une relation associative')

@section('content')
<div class="container-fluid">
    <h1>Ajouter une relation associative à "{{ $term->preferred_label }}"</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('thesaurus.associative_relations.store', $term->id) }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="related_term_id">Terme associé <span class="text-danger">*</span></label>
                            <select name="related_term_id" id="related_term_id" class="form-control @error('related_term_id') is-invalid @enderror" required>
                                <option value="">Sélectionner un terme</option>
                                @foreach($terms as $relatedTerm)
                                    <option value="{{ $relatedTerm->id }}">{{ $relatedTerm->preferred_label }}</option>
                                @endforeach
                            </select>
                            @error('related_term_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="relation_subtype">Type de relation <span class="text-danger">*</span></label>
                            <select name="relation_subtype" id="relation_subtype" class="form-control @error('relation_subtype') is-invalid @enderror" required>
                                @foreach($relationSubtypes as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('relation_subtype')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('thesaurus.associative_relations.index', $term->id) }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const termSelect = document.getElementById('related_term_id');

        // Ajouter une barre de recherche pour le select
        $(termSelect).select2({
            placeholder: 'Rechercher un terme...',
            allowClear: true
        });
    });
</script>
@endsection
