@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>Détails du Support</h1>
        </div>
        <div class="card-body">
            <h5 class="card-title">{{ $recordSupport->name }}</h5>
            <p class="card-text">{{ $recordSupport->description }}</p>
            <a href="{{ route('record-supports.index') }}" class="btn btn-primary btn-sm">Retour à la liste des supports</a>
            <a href="{{ route('record-supports.edit', $recordSupport->id) }}" class="btn btn-warning btn-sm">Modifier</a>
            <form action="{{ route('record-supports.destroy', $recordSupport->id) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce support ?')">Supprimer</button>
            </form>
        </div>
    </div>
</div>
@endsection
