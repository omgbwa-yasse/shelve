@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Nouvelle demande de document</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('public.document-requests.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="document_type">Type de document</label>
                            <select class="form-control @error('document_type') is-invalid @enderror" id="document_type" name="document_type" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="certificate" {{ old('document_type') == 'certificate' ? 'selected' : '' }}>Certificat</option>
                                <option value="report" {{ old('document_type') == 'report' ? 'selected' : '' }}>Rapport</option>
                                <option value="statement" {{ old('document_type') == 'statement' ? 'selected' : '' }}>Relevé</option>
                                <option value="other" {{ old('document_type') == 'other' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('document_type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                            <small class="form-text text-muted">Décrivez en détail le document que vous souhaitez obtenir</small>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="requested_date">Date souhaitée</label>
                            <input type="date" class="form-control @error('requested_date') is-invalid @enderror"
                                   id="requested_date" name="requested_date"
                                   value="{{ old('requested_date', now()->addDays(7)->format('Y-m-d')) }}" required>
                            @error('requested_date')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="attachments">Pièces jointes</label>
                            <input type="file" class="form-control @error('attachments') is-invalid @enderror"
                                   id="attachments" name="attachments[]" multiple>
                            <small class="form-text text-muted">Vous pouvez joindre plusieurs fichiers (PDF, images, etc.)</small>
                            @error('attachments')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="additional_info">Informations complémentaires</label>
                            <textarea class="form-control @error('additional_info') is-invalid @enderror"
                                      id="additional_info" name="additional_info" rows="3">{{ old('additional_info') }}</textarea>
                            @error('additional_info')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Soumettre la demande</button>
                            <a href="{{ route('public.document-requests.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
