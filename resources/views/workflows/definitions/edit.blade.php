@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-pencil"></i> {{ __('Modifier Définition de Workflow') }}</h1>
        <a href="{{ route('workflows.definitions.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('workflows.definitions.update', $definition) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Nom') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $definition->name) }}" required maxlength="100">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="3">{{ old('description', $definition->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">{{ __('Statut') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="draft" {{ old('status', $definition->status) == 'draft' ? 'selected' : '' }}>{{ __('Brouillon') }}</option>
                        <option value="active" {{ old('status', $definition->status) == 'active' ? 'selected' : '' }}>{{ __('Actif') }}</option>
                        <option value="archived" {{ old('status', $definition->status) == 'archived' ? 'selected' : '' }}>{{ __('Archivé') }}</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="bpmn_xml" class="form-label">{{ __('Configuration BPMN') }} <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('bpmn_xml') is-invalid @enderror"
                              id="bpmn_xml" name="bpmn_xml" rows="10" required>{{ old('bpmn_xml', $definition->bpmn_xml) }}</textarea>
                    @error('bpmn_xml')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">{{ __('Configuration XML au format BPMN 2.0') }}</div>
                </div>

                <div class="alert alert-info">
                    <strong>{{ __('Version actuelle:') }}</strong> v{{ $definition->version }}<br>
                    <strong>{{ __('Créé par:') }}</strong> {{ $definition->creator->name ?? 'N/A' }}<br>
                    <strong>{{ __('Date de création:') }}</strong> {{ $definition->created_at->format('d/m/Y H:i') }}
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('workflows.definitions.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> {{ __('Mettre à jour') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
