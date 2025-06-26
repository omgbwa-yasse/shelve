@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Modifier le document</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('public.records.update', $record) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="title">Titre</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title', $record->title) }}" required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="record_type">Type de document</label>
                            <input type="text" class="form-control @error('record_type') is-invalid @enderror"
                                   id="record_type" name="record_type" value="{{ old('record_type', $record->record_type) }}" required>
                            @error('record_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="reference_number">Numéro de référence</label>
                            <input type="text" class="form-control @error('reference_number') is-invalid @enderror"
                                   id="reference_number" name="reference_number" value="{{ old('reference_number', $record->reference_number) }}" required>
                            @error('reference_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="6" required>{{ old('description', $record->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="status">Statut</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="draft" {{ old('status', $record->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="published" {{ old('status', $record->status) == 'published' ? 'selected' : '' }}>Publié</option>
                                <option value="archived" {{ old('status', $record->status) == 'archived' ? 'selected' : '' }}>Archivé</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="attachments">Nouvelles pièces jointes</label>
                            <input type="file" class="form-control @error('attachments.*') is-invalid @enderror"
                                   id="attachments" name="attachments[]" multiple>
                            @error('attachments.*')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        @if($record->attachments && $record->attachments->count() > 0)
                            <div class="form-group mb-3">
                                <label>Pièces jointes existantes :</label>
                                @foreach($record->attachments as $attachment)
                                    <div class="border p-2 mb-2 rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>{{ $attachment->original_name }}</strong>
                                                <small class="text-muted">({{ number_format($attachment->size / 1024, 2) }} KB)</small>
                                            </div>
                                            <a href="{{ asset('storage/' . $attachment->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank">Voir</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="{{ route('public.records.show', $record) }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
