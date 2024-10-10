
@extends('layouts.app')

@section('content')
    <h1>Créer une nouvelle sauvegarde</h1>

    @if(session()->has('success'))
        <div class="alert alert-success">{{ session()->get('success') }}</div>
    @endif


    <form method="POST" action="{{ route('backups.store') }}">
        @csrf

        <div class="form-group">
            <label for="type">Type de sauvegarde</label>
            <select class="form-control" id="type" name="type" required>
                <option value="">Sélectionnez un type</option>
                <option value="metadata">Metadata</option>
                <option value="full">Complète</option>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Description de la sauvegarde</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label for="status">Statut de la sauvegarde</label>
            <select class="form-control" id="status" name="status" required>
                <option value="">Sélectionnez un statut</option>
                <option value="in_progress">En cours</option>
                <option value="success">Réussie</option>
                <option value="failed">Échouée</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Créer la sauvegarde</button>
    </form>
@endsection
