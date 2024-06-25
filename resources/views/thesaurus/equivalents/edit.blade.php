@extends('layouts.app')

@section('content')

<div class="container">
    <h1 class="mt-4">Modifier la equivalent de terme</h1>

    <form action="{{ route('term-equivalent-types.update', $termEquivalent->id) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="code" class="form-label">Nom :</label>
            <input type="text" name="code" id="code" class="form-control" value="{{ $termEquivalent->code }}" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Nom :</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $termEquivalent->name }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description :</label>
            <textarea name="description" id="description" class="form-control">{{ $termEquivalent->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>

    <a href="{{ route('term-equivalent-types.index') }}" class="mt-3 d-block">Retour à la liste</a>
</div>

@endsection
