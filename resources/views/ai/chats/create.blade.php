@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Créer un nouveau Chat AI</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('ai.chats.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Titre</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ai_model_id" class="form-label">Modèle AI</label>
                            <select class="form-select @error('ai_model_id') is-invalid @enderror"
                                    id="ai_model_id" name="ai_model_id" required>
                                <option value="">Sélectionnez un modèle</option>
                                @foreach($aiModels as $model)
                                    <option value="{{ $model->id }}"
                                        {{ old('ai_model_id') == $model->id ? 'selected' : '' }}>
                                        {{ $model->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ai_model_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror"
                                       id="is_active" name="is_active" value="1"
                                       {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Actif</label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('ai.chats.index') }}" class="btn btn-secondary">Retour</a>
                            <button type="submit" class="btn btn-primary">Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
