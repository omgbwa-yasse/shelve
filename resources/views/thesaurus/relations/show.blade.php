@extends('layouts.app')

@section('content')

<div class="container">

    <h1 class="mt-4">{{ $termRelation->code }}</h1>

    <p>{{ $termRelation->name }}</p>

    <p>{{ $termRelation->description }}</p>

    <a href="{{ route('term-relation-types.index') }}" class="btn btn-secondary mb-3">Retour à la liste</a>

    <a href="{{ route('term-relation-types.edit', $termRelation->id) }}" class="btn btn-primary mb-3">Modifier</a>

    <form action="{{ route('term-relation-types.destroy', $termRelation->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Supprimer</button>
    </form>
</div>

@endsection
