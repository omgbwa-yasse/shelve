@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Modifier la réponse</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('public.responses.update', $response) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label>Demande associée</label>
                            <div class="form-control-plaintext">
                                @if($response->documentRequest)
                                    <a href="{{ route('public.document-requests.show', $response->documentRequest) }}">
                                        {{ $response->documentRequest->title }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="content">Contenu de la réponse</label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="6" required>{{ old('content', $response->content) }}</textarea>
                            @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="status">Statut</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="draft" {{ old('status', $response->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="sent" {{ old('status', $response->status) == 'sent' ? 'selected' : '' }}>Envoyé</option>
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

                        @if($response->attachments && $response->attachments->count() > 0)
                            <div class="form-group mb-3">
                                <label>Pièces jointes existantes :</label>
                                @foreach($response->attachments as $attachment)
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
                            <a href="{{ route('public.responses.show', $response) }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
