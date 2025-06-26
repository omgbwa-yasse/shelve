@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Créer un nouveau document</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('public.records.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="record_id">Document à publier</label>
                            <select class="form-control @error('record_id') is-invalid @enderror" id="record_id" name="record_id" required>
                                <option value="">Sélectionner un document</option>
                                @foreach(\App\Models\Record::all() as $recordOption)
                                    <option value="{{ $recordOption->id }}" {{ old('record_id') == $recordOption->id ? 'selected' : '' }}>
                                        {{ $recordOption->name }} ({{ $recordOption->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('record_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="published_at">Date de publication (optionnel)</label>
                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror"
                                   id="published_at" name="published_at" value="{{ old('published_at') }}">
                            <small class="form-text text-muted">Si vide, la date actuelle sera utilisée</small>
                            @error('published_at')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="expires_at">Date d'expiration (optionnel)</label>
                            <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror"
                                   id="expires_at" name="expires_at" value="{{ old('expires_at') }}">
                            <small class="form-text text-muted">Laissez vide si le document n'expire pas</small>
                            @error('expires_at')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="publication_notes">Notes de publication (optionnel)</label>
                            <textarea class="form-control @error('publication_notes') is-invalid @enderror"
                                      id="publication_notes" name="publication_notes" rows="4">{{ old('publication_notes') }}</textarea>
                            @error('publication_notes')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="attachments">Pièces jointes</label>
                            <input type="file" class="form-control @error('attachments.*') is-invalid @enderror"
                                   id="attachments" name="attachments[]" multiple>
                            @error('attachments.*')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Créer le document</button>
                            <a href="{{ route('public.records.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
