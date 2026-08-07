@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Modifier la pièce jointe</h2>
                </div>

                <div class="card-body">
                    <form action="{{ route('public.response-attachments.update', $attachment) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="response_id" class="form-label">Réponse</label>
                            <select class="form-control @error('response_id') is-invalid @enderror" id="response_id" name="response_id" required>
                                <option value="">Sélectionner une réponse</option>
                                @foreach($responses as $response)
                                    <option value="{{ $response->id }}" {{ (old('response_id', $attachment->response_id) == $response->id) ? 'selected' : '' }}>
                                        {{ Str::limit($response->content, 100) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('response_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Nouveau fichier (optionnel)</label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            <small class="form-text text-muted">Fichier actuel : {{ $attachment->original_name }}</small>
                            @error('file')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $attachment->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('public.response-attachments.index') }}" class="btn btn-secondary">Retour</a>
                            <button type="submit" class="btn btn-primary">Modifier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
