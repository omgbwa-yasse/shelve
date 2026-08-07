@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Modifier la demande de document #{{ $documentRequest->reference }}</h2>
                </div>

                <div class="card-body">
                    <form action="{{ route('public.document-requests.update', $documentRequest) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="document_type" class="form-label">Type de document</label>
                            <select class="form-select @error('document_type') is-invalid @enderror" id="document_type" name="document_type" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="certificate" {{ old('document_type', $documentRequest->document_type) === 'certificate' ? 'selected' : '' }}>Certificat</option>
                                <option value="report" {{ old('document_type', $documentRequest->document_type) === 'report' ? 'selected' : '' }}>Rapport</option>
                                <option value="statement" {{ old('document_type', $documentRequest->document_type) === 'statement' ? 'selected' : '' }}>Relevé</option>
                                <option value="other" {{ old('document_type', $documentRequest->document_type) === 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('document_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description', $documentRequest->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="requested_date" class="form-label">Date souhaitée</label>
                            <input type="date" class="form-control @error('requested_date') is-invalid @enderror" id="requested_date" name="requested_date" value="{{ old('requested_date', $documentRequest->requested_date->format('Y-m-d')) }}" required>
                            @error('requested_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="additional_info" class="form-label">Informations complémentaires</label>
                            <textarea class="form-control @error('additional_info') is-invalid @enderror" id="additional_info" name="additional_info" rows="3">{{ old('additional_info', $documentRequest->additional_info) }}</textarea>
                            @error('additional_info')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="attachments" class="form-label">Pièces jointes</label>
                            <input type="file" class="form-control @error('attachments') is-invalid @enderror" id="attachments" name="attachments[]" multiple>
                            @error('attachments')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Vous pouvez sélectionner plusieurs fichiers. Les fichiers existants seront conservés.</div>
                        </div>

                        @if($documentRequest->attachments->count() > 0)
                            <div class="mb-3">
                                <label class="form-label">Pièces jointes actuelles</label>
                                <ul class="list-unstyled">
                                    @foreach($documentRequest->attachments as $attachment)
                                        <li>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>{{ $attachment->original_name }}</span>
                                                <form action="{{ route('public.response-attachments.destroy', $attachment) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette pièce jointe ?')">Supprimer</button>
                                                </form>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('public.document-requests.show', $documentRequest) }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
