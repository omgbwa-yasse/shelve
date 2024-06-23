@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Shelves</h1>
        <a href="{{ route('shelves.create') }}" class="btn btn-primary mb-3">Create Shelf</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Observation</th>
                    <th>Face(s)</th>
                    <th>Travée</th>
                    <th>Tabllettes</th>
                    <th>Longueur tablette</th>
                    <th>Salle d'archives</th>
                    <th>Volumétrie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shelves as $shelf)
                    <tr>
                        <td>{{ $shelf->id }}</td>
                        <td>{{ $shelf->code }}</td>
                        <td>{{ $shelf->observation }}</td>
                        <td>{{ $shelf->face }}</td>
                        <td>{{ $shelf->ear }}</td>
                        <td>{{ $shelf->shelf }}</td>
                        <td>{{ $shelf->shelf_length }} Cm </td>
                        <td>{{ $shelf->room->name }}</td>
                        <td>{{  ($shelf->face * $shelf->ear * $shelf->shelf * $shelf->shelf_length)/100 }} ml </td>
                        <td>
                            <a href="{{ route('shelves.show', $shelf->id) }}" class="btn btn-info btn-sm">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
