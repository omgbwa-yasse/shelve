@extends('layouts.app')

@section('title', 'Ajouter un alignement externe')

@section('content')
<div class="container-fluid">
    <h1>Ajouter un alignement externe à "{{ $term->preferred_label }}"</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('terms.external-alignments.store', $term->id) }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="external_uri">URI externe <span class="text-danger">*</span></label>
                            <input type="url" name="external_uri" id="external_uri" class="form-control @error('external_uri') is-invalid @enderror" value="{{ old('external_uri') }}" required>
                            <small class="form-text text-muted">L'URI du concept externe (ex: http://www.wikidata.org/entity/Q42)</small>
                            @error('external_uri')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="external_label">Libellé externe</label>
                            <input type="text" name="external_label" id="external_label" class="form-control @error('external_label') is-invalid @enderror" value="{{ old('external_label') }}">
                            <small class="form-text text-muted">Le libellé du concept dans le référentiel externe</small>
                            @error('external_label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="external_vocabulary">Vocabulaire externe <span class="text-danger">*</span></label>
                            <input type="text" name="external_vocabulary" id="external_vocabulary" class="form-control @error('external_vocabulary') is-invalid @enderror" value="{{ old('external_vocabulary') }}" required>
                            <small class="form-text text-muted">Le nom du vocabulaire externe (ex: Wikidata, RAMEAU, MeSH)</small>
                            @error('external_vocabulary')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="match_type">Type de correspondance <span class="text-danger">*</span></label>
                            <select name="match_type" id="match_type" class="form-control @error('match_type') is-invalid @enderror" required>
                                @foreach($matchTypes as $code => $name)
                                    <option value="{{ $code }}" {{ old('match_type') == $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Le type de correspondance selon SKOS</small>
                            @error('match_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="{{ route('terms.external-alignments.index', $term->id) }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
