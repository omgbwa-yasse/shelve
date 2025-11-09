@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-pencil"></i> {{ __('Modifier la collection') }}</h1>
        <a href="{{ route('museum.collections.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('museum.collections.update', $collection ?? 1) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ __('Cette fonctionnalité est en cours de développement. Les collections sont gérées via les catégories d\'artefacts.') }}
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Nom de la collection') }}</label>
                            <input type="text" class="form-control" id="name" name="name" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="pieces_count" class="form-label">{{ __('Nombre de pièces') }}</label>
                            <input type="text" class="form-control" id="pieces_count" readonly>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control" id="description" name="description" rows="4" readonly></textarea>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('museum.collections.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> {{ __('Retour') }}
                    </a>
                    <button type="submit" class="btn btn-primary" disabled>
                        <i class="bi bi-save"></i> {{ __('Enregistrer') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
