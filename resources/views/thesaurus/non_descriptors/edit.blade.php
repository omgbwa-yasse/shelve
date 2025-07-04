@extends('layouts.app')

@section('title', 'Modifier un non-descripteur')

@section('content')
<div class="container-fluid">
    <h1>Modifier le non-descripteur "{{ $nonDescriptor->non_descriptor_label }}"</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('terms.non-descriptors.update', [$term->id, $nonDescriptor->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="non_descriptor_label">Libellé du non-descripteur <span class="text-danger">*</span></label>
                            <input type="text" name="non_descriptor_label" id="non_descriptor_label" class="form-control @error('non_descriptor_label') is-invalid @enderror" value="{{ old('non_descriptor_label', $nonDescriptor->non_descriptor_label) }}" required>
                            @error('non_descriptor_label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="relation_type">Type de relation <span class="text-danger">*</span></label>
                            <select name="relation_type" id="relation_type" class="form-control @error('relation_type') is-invalid @enderror" required>
                                @foreach($relationTypes as $code => $name)
                                    <option value="{{ $code }}" {{ old('relation_type', $nonDescriptor->relation_type) == $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('relation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-check">
                            <input type="checkbox" name="hidden" id="hidden" class="form-check-input" value="1" {{ old('hidden', $nonDescriptor->hidden) ? 'checked' : '' }}>
                            <label class="form-check-label" for="hidden">Caché (SKOS hiddenLabel)</label>
                        </div>
                        <small class="text-muted">Les termes cachés sont utilisés uniquement pour la recherche mais ne sont pas affichés.</small>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <a href="{{ route('terms.non-descriptors.index', $term->id) }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
