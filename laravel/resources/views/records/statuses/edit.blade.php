@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier le Statut d'Enregistrement</h1>
    <form action="{{ route('record-statuses.update', $recordStatus->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Nom</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $recordStatus->name }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description">{{ $recordStatus->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        <a href="{{ route('record-statuses.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
