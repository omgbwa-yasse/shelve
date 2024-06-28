@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Détails du Statut d'Enregistrement</h1>
        </div>
        <div class="card-body">
            <h5 class="card-title">{{ $recordStatus->name }}</h5>
            <p class="card-text">{{ $recordStatus->description }}</p>
            <a href="{{ route('record-statuses.index') }}" class="btn btn-primary btn-sm">Retour à la liste des statuts</a>
            <a href="{{ route('record-statuses.edit', $recordStatus->id) }}" class="btn btn-warning btn-sm">Modifier</a>
            <form action="{{ route('record-statuses.destroy', $recordStatus->id) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce statut ?')">Supprimer</button>
            </form>
        </div>
    </div>
</div>
@endsection
