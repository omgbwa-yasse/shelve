@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Modifier le Support</h1>
    <form action="{{ route('record-supports.update', $recordSupport->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Nom</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $recordSupport->name }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description">{{ $recordSupport->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        <a href="{{ route('record-supports.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
