@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-play-fill"></i> {{ __('Démarrer un Workflow') }}</h1>
        <a href="{{ route('workflows.instances.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('workflows.instances.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="definition_id" class="form-label">{{ __('Définition de workflow') }} <span class="text-danger">*</span></label>
                    <select class="form-select @error('definition_id') is-invalid @enderror" id="definition_id" name="definition_id" required>
                        <option value="">{{ __('Sélectionnez une définition') }}</option>
                        @foreach($definitions as $definition)
                            <option value="{{ $definition->id }}" {{ old('definition_id', request('definition')) == $definition->id ? 'selected' : '' }}>
                                {{ $definition->name }} (v{{ $definition->version }})
                                @if($definition->status == 'draft') - {{ __('Brouillon') }} @endif
                            </option>
                        @endforeach
                    </select>
                    @error('definition_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Nom de l\'instance') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required maxlength="190">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">{{ __('Donnez un nom descriptif à cette exécution de workflow') }}</div>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    {{ __('Une fois démarré, le workflow créera automatiquement les tâches selon la définition BPMN configurée.') }}
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('workflows.instances.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-play-fill"></i> {{ __('Démarrer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
