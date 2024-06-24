@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">{{ $termType->name }}</h1>
    <p>{{ $termType->description }}</p>
    <a href="{{ route('term-types.index') }}" class="btn btn-secondary">Retour à la liste</a>
    <a href="{{ route('term-types.edit', $termType->id) }}" class="btn btn-primary">Modifier</a>
    <form action="{{ route('term-types.destroy', $termType->id) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Supprimer</button>
    </form>
</div>
@endsection
