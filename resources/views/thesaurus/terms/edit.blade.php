@extends('layouts.app')

@section('title', 'Modifier un terme')

@section('content')
    <div class="container-fluid">
        <h1>Modifier le terme : {{ $term->preferred_label }}</h1>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('terms.update', $term->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="preferred_label">Libellé préféré <span class="text-danger">*</span></label>
                                <input type="text" name="preferred_label" id="preferred_label" class="form-control @error('preferred_label') is-invalid @enderror" value="{{ old('preferred_label', $term->preferred_label) }}" required>
                                @error('preferred_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="language">Langue <span class="text-danger">*</span></label>
                                <select name="language" id="language" class="form-control @error('language') is-invalid @enderror" required>
                                    @foreach($languages as $code => $name)
                                        <option value="{{ $code }}" {{ old('language', $term->language) == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Statut <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                    @foreach($statuses as $code => $name)
                                        <option value="{{ $code }}" {{ old('status', $term->status) == $code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category">Catégorie / Domaine</label>
                                <input type="text" name="category" id="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $term->category) }}">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="notation">Notation / Code</label>
                                <input type="text" name="notation" id="notation" class="form-control @error('notation') is-invalid @enderror" value="{{ old('notation', $term->notation) }}">
                                @error('notation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group pt-4 mt-2">
                                <div class="form-check">
                                    <input type="checkbox" name="is_top_term" id="is_top_term" class="form-check-input" {{ old('is_top_term', $term->is_top_term) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_top_term">Terme de tête</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="definition">Définition</label>
                                <textarea name="definition" id="definition" class="form-control @error('definition') is-invalid @enderror" rows="3">{{ old('definition', $term->definition) }}</textarea>
                                @error('definition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="scope_note">Note d'application</label>
                                <textarea name="scope_note" id="scope_note" class="form-control @error('scope_note') is-invalid @enderror" rows="3">{{ old('scope_note', $term->scope_note) }}</textarea>
                                @error('scope_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="history_note">Note historique</label>
                                <textarea name="history_note" id="history_note" class="form-control @error('history_note') is-invalid @enderror" rows="3">{{ old('history_note', $term->history_note) }}</textarea>
                                @error('history_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="example">Exemple</label>
                                <textarea name="example" id="example" class="form-control @error('example') is-invalid @enderror" rows="3">{{ old('example', $term->example) }}</textarea>
                                @error('example')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="editorial_note">Note éditoriale</label>
                                <textarea name="editorial_note" id="editorial_note" class="form-control @error('editorial_note') is-invalid @enderror" rows="3">{{ old('editorial_note', $term->editorial_note) }}</textarea>
                                @error('editorial_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        <a href="{{ route('terms.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
