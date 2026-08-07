@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Créer une réponse</h2>
                    @if(isset($documentRequest))
                        <p class="mb-0">En réponse à : <strong>{{ $documentRequest->title }}</strong></p>
                    @endif
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('public.responses.store') }}" enctype="multipart/form-data">
                        @csrf

                        @if(isset($documentRequest))
                            <input type="hidden" name="document_request_id" value="{{ $documentRequest->id }}">
                        @else
                            <div class="form-group mb-3">
                                <label for="document_request_id">Demande de document</label>
                                <select class="form-control @error('document_request_id') is-invalid @enderror" id="document_request_id" name="document_request_id" required>
                                    <option value="">Sélectionnez une demande</option>
                                    <!-- Options à remplir dynamiquement -->
                                </select>
                                @error('document_request_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        @endif

                        <div class="form-group mb-3">
                            <label for="content">Contenu de la réponse</label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="6" required>{{ old('content') }}</textarea>
                            @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="status">Statut</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="sent" {{ old('status') == 'sent' ? 'selected' : '' }}>Envoyé</option>
                            </select>
                            @error('status')
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
                            <button type="submit" class="btn btn-primary">Créer la réponse</button>
                            <a href="{{ route('public.responses.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
