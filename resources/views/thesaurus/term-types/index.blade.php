@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">Types de termes</h1>
    <a href="{{ route('term-types.create') }}" class="btn btn-primary mb-3">Créer un nouveau type de terme</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($termTypes as $termType)
            <tr>
                <td>{{ $termType->id }}</td>
                <td>{{ $termType->code }}</td>
                <td>{{ $termType->name }}</td>
                <td>{{ $termType->description }}</td>
                <td>
                    <a href="{{ route('term-types.show', $termType->id) }}" class="btn btn-secondary">Voir</a>
                    <a href="{{ route('term-types.edit', $termType->id) }}" class="btn btn-primary">Modifier</a>
                    <form action="{{ route('term-types.destroy', $termType->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
