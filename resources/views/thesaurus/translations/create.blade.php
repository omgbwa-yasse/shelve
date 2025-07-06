@extends('layouts.app')

@section('title', 'Ajouter une traduction')

@section('content')
<div class="container-fluid">
    <h1>Ajouter une traduction à "{{ $term->preferred_label }}" ({{ $term->language }})</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('terms.translations.store', $term->id) }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="target_term_id">Terme traduit <span class="text-danger">*</span></label>
                            <select name="target_term_id" id="target_term_id" class="form-control @error('target_term_id') is-invalid @enderror" required>
                                <option value="">Sélectionner un terme</option>
                                @foreach($terms as $translatedTerm)
                                    <option value="{{ $translatedTerm->id }}">
                                        {{ $translatedTerm->preferred_label }} [{{ strtoupper($translatedTerm->language) }}]
                                    </option>
                                @endforeach
                            </select>
                            @error('target_term_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('terms.translations.index', $term->id) }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const termSelect = document.getElementById('target_term_id');

        // Ajouter une barre de recherche pour le select
        $(termSelect).select2({
            placeholder: 'Rechercher un terme...',
            allowClear: true
        });
    });
</script>
@endsection
