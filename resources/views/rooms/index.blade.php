@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Rooms</h1>
        <a href="{{ route('rooms.create') }}" class="btn btn-primary mb-3">Ajouter une salle</a>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Floor</th>
                    <th>Building</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rooms as $room)
                    <tr>
                        <td>{{ $room->id }}</td>
                        <td>{{ $room->code }}</td>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->description }}</td>
                        <td>{{ $room->floor->name }}</td>
                        <td>{{ $room->floor->building->name }}</td>
                        <td>
                            <a href="{{ route('rooms.show', $room->id) }}" class="btn btn-info btn-sm">Paramètres</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
