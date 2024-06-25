@extends('layouts.app')

@section('content')

<div class="container">

    <h1 class="mt-4">{{ $termEquivalent->code }}</h1>

    <p>{{ $termEquivalent->name }}</p>

    <p>{{ $termEquivalent->description }}</p>

    <a href="{{ route('term-equivalent-types.index') }}" class="btn btn-secondary mb-3">Retour à la liste</a>

    <a href="{{ route('term-equivalent-types.edit', $termEquivalent->id) }}" class="btn btn-primary mb-3">Modifier</a>

    <form action="{{ route('term-equivalent-types.destroy', $termEquivalent->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Supprimer</button>
    </form>
</div>

@endsection
