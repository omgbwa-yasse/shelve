@extends('layouts.app')

@section('content')

<div class="container">
    <h1 class="mt-4">Equivalents de termes</h1>

    <a href="{{ route('term-equivalent-types.create') }}" class="btn btn-primary mb-3">Créer une nouvelle equivalent de terme</a>

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
            @foreach ($termEquivalents as $termEquivalent)
                <tr>
                    <td>{{ $termEquivalent->id }}</td>
                    <td>{{ $termEquivalent->code }}</td>
                    <td>{{ $termEquivalent->name }}</td>
                    <td>{{ $termEquivalent->description }}</td>
                    <td>
                        <a href="{{ route('term-equivalent-types.show', $termEquivalent->id) }}" class="btn btn-secondary">Voir</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
