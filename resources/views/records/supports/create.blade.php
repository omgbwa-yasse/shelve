@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ajouter un Support</h1>
    <form action="{{ route('record-supports.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nom</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="{{ route('record-supports.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
