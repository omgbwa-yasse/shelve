@extends('layouts.app')

@section('content')

<div class="container">
    <h1 class="mt-4">Relations de termes</h1>

    <a href="{{ route('term-relations.create') }}" class="btn btn-primary mb-3">Créer une nouvelle relation de terme</a>

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
            @foreach ($termRelations as $termRelation)
                <tr>
                    <td>{{ $termRelation->id }}</td>
                    <td>{{ $termRelation->code }}</td>
                    <td>{{ $termRelation->name }}</td>
                    <td>{{ $termRelation->description }}</td>
                    <td>
                        <a href="{{ route('term-relations.show', $termRelation->id) }}" class="btn btn-secondary">Voir</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
