@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Backup Details</h1>

        <div class="card mb-4">
            <div class="card-body">
                <p><strong>Date:</strong> {{ $backup->date_time }}</p>
                <p><strong>Type:</strong> {{ $backup->type }}</p>
                <p><strong>Description:</strong> {{ $backup->description ?? 'Aucune description' }}</p>
                <p><strong>Status:</strong> {{ $backup->status }}</p>
                <p><strong>User:</strong> {{ $backup->user->name ?? 'Utilisateur inconnu' }}</p>
                <p><strong>Size:</strong> {{ number_format($backup->size / 1024, 2) }} KB</p>
                <p><strong>Backup File:</strong> {{ $backup->backup_file }}</p>
                <p><strong>Path:</strong> {{ $backup->path }}</p>
            </div>
        </div>

        <div class="d-flex justify-content-start">
            <a href="{{ route('backups.index') }}" class="btn btn-sm btn-secondary me-2">Retour</a>
            <a href="{{ route('backups.edit', $backup->id) }}" class="btn btn-sm btn-warning me-2">Éditer</a>
            <form action="{{ route('backups.destroy', $backup->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce backup ?');" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
            </form>
        </div>
    </div>

@endsection
