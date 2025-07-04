@extends('layouts.app')

@section('title', 'Ajouter un terme spécifique')

@section('content')
<div class="container-fluid">
    <h1>Ajouter un terme spécifique à "{{ $term->preferred_label }}"</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('terms.hierarchical-relations.narrower.store', $term->id) }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="narrower_term_id">Terme spécifique <span class="text-danger">*</span></label>
                            <select name="narrower_term_id" id="narrower_term_id" class="form-control @error('narrower_term_id') is-invalid @enderror" required>
                                <option value="">Sélectionner un terme</option>
                                @foreach($terms as $narrowerTerm)
                                    <option value="{{ $narrowerTerm->id }}">{{ $narrowerTerm->preferred_label }}</option>
                                @endforeach
                            </select>
                            @error('narrower_term_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="relation_type">Type de relation <span class="text-danger">*</span></label>
                            <select name="relation_type" id="relation_type" class="form-control @error('relation_type') is-invalid @enderror" required>
                                @foreach($relationTypes as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('relation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('terms.hierarchical-relations.index', $term->id) }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const termSelect = document.getElementById('narrower_term_id');

        // Ajouter une barre de recherche pour le select
        $(termSelect).select2({
            placeholder: 'Rechercher un terme...',
            allowClear: true
        });
    });
</script>
@endsection
