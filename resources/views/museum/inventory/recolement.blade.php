@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-check2-square"></i> {{ __('Récolement des collections') }}</h1>
        <a href="{{ route('museum.inventory.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="bi bi-info-circle"></i> {{ __('Information') }}</h5>
        </div>
        <div class="card-body">
            <p>{{ __('Le récolement consiste à vérifier la présence et l\'état des artefacts dans les collections.') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('museum.inventory.recolement.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="collection_id" class="form-label">{{ __('Collection') }}</label>
                    <select class="form-select" id="collection_id" name="collection_id">
                        <option value="">{{ __('Toutes les collections') }}</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="location" class="form-label">{{ __('Localisation') }}</label>
                    <input type="text" class="form-control" id="location" name="location">
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('museum.inventory.index') }}" class="btn btn-secondary">
                        {{ __('Annuler') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> {{ __('Démarrer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
