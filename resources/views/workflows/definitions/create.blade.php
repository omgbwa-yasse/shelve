@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-plus-circle"></i> {{ __('Nouvelle Définition de Workflow') }}</h1>
        <a href="{{ route('workflows.definitions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> {{ __('Informations de base') }}</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-lightbulb"></i>
                        {{ __('Créez d\'abord la définition du workflow, puis vous pourrez configurer le diagramme BPMN.') }}
                    </div>

                    <form action="{{ route('workflows.definitions.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label">
                                {{ __('Nom du Workflow') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   maxlength="100"
                                   placeholder="{{ __('Ex: Processus de validation des demandes') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle-fill"></i> {{ __('Donnez un nom descriptif à votre workflow') }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="4"
                                      placeholder="{{ __('Décrivez l\'objectif et le fonctionnement de ce workflow...') }}">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle-fill"></i> {{ __('Optionnel : Ajoutez des détails sur ce workflow') }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label">
                                {{ __('Statut') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror"
                                    id="status"
                                    name="status"
                                    required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>
                                    📝 {{ __('Brouillon') }}
                                </option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                    ✅ {{ __('Actif') }}
                                </option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>
                                    📦 {{ __('Archivé') }}
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle-fill"></i> {{ __('Utilisez "Brouillon" pour les workflows en cours de conception') }}
                            </div>
                        </div>

                        <!-- Champ caché pour BPMN XML par défaut -->
                        <input type="hidden" name="bpmn_xml" value='<?xml version="1.0" encoding="UTF-8"?>
<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL" xmlns:bpmndi="http://www.omg.org/spec/BPMN/20100524/DI" xmlns:dc="http://www.omg.org/spec/DD/20100524/DC" id="Definitions_empty">
  <bpmn:process id="Process_1" isExecutable="true">
    <!-- Configuration à définir ultérieurement -->
  </bpmn:process>
</bpmn:definitions>'>

                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title"><i class="bi bi-arrow-right-circle"></i> {{ __('Prochaine étape') }}</h6>
                                <p class="card-text mb-0">
                                    {{ __('Après création, vous serez redirigé vers l\'interface de configuration BPMN drag & drop.') }}
                                </p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('workflows.definitions.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save"></i> {{ __('Créer le Workflow') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
