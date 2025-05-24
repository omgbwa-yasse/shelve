@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Nouvelle Conversation IA</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('ai-chats.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="title" class="form-label">Titre de la conversation</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title') }}"
                                       placeholder="Ex: Analyse des dossiers Q1 2024">
                                <small class="form-text text-muted">
                                    Laissez vide pour générer automatiquement un titre basé sur votre première question
                                </small>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="ai_model_id" class="form-label">Modèle IA <span class="text-danger">*</span></label>
                                <select class="form-select @error('ai_model_id') is-invalid @enderror"
                                        id="ai_model_id" name="ai_model_id" required>
                                    <option value="">Sélectionnez un modèle</option>
                                    @foreach($models as $provider => $providerModels)
                                        <optgroup label="{{ $provider }}">
                                            @foreach($providerModels as $model)
                                                <option value="{{ $model->id }}"
                                                        data-capabilities="{{ json_encode($model->capabilities) }}"
                                                        {{ old('ai_model_id') == $model->id ? 'selected' : '' }}>
                                                    {{ $model->name }} ({{ $model->version }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('ai_model_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="modelCapabilities" class="mt-2"></div>
                            </div>

                            <div class="mb-3">
                                <label for="initial_message" class="form-label">Message initial (optionnel)</label>
                                <textarea class="form-control @error('initial_message') is-invalid @enderror"
                                          id="initial_message" name="initial_message" rows="4"
                                          placeholder="Posez votre première question ou décrivez ce que vous souhaitez accomplir...">{{ old('initial_message') }}</textarea>
                                @error('initial_message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Ressources à inclure (optionnel)</label>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <h6><i class="bi bi-file-text"></i> Dossiers</h6>
                                                <input type="text" class="form-control form-control-sm"
                                                       name="resources[records]"
                                                       placeholder="IDs séparés par des virgules">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <h6><i class="bi bi-envelope"></i> Courriers</h6>
                                                <input type="text" class="form-control form-control-sm"
                                                       name="resources[mail]"
                                                       placeholder="IDs séparés par des virgules">
                                            </div>
                                            <div class="col-md-6">
                                                <h6><i class="bi bi-file-earmark"></i> Bordereaux</h6>
                                                <input type="text" class="form-control form-control-sm"
                                                       name="resources[slip]"
                                                       placeholder="IDs séparés par des virgules">
                                            </div>
                                            <div class="col-md-6">
                                                <h6><i class="bi bi-chat-dots"></i> Communications</h6>
                                                <input type="text" class="form-control form-control-sm"
                                                       name="resources[communication]"
                                                       placeholder="IDs séparés par des virgules">
                                            </div>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            <i class="bi bi-info-circle"></i> Les ressources vous permettent de contextualiser la conversation avec des données spécifiques
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Options avancées</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                           value="1" checked>
                                    <label class="form-check-label" for="is_active">
                                        Conversation active
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('ai-chats.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Créer la conversation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Conseils d'utilisation -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Conseils d'utilisation</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Choisissez le modèle en fonction de vos besoins : GPT-4 pour des tâches complexes, GPT-3.5 pour des questions simples</li>
