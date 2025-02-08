<!-- resources/views/bulletin-boards/events/index.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Liste des Événements</h1>
        <a href="{{ route('bulletin-boards.events.create') }}" class="btn btn-primary mb-3">Créer un Événement</a>
        <table class="table">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Date de Début</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($events as $event)
                <tr>
                    <td>{{ $event->name }}</td>
                    <td>{{ $event->description }}</td>
                    <td>{{ $event->start_date }}</td>
                    <td>
                        <a href="{{ route('bulletin-boards.events.show', $event) }}" class="btn btn-info">Voir</a>
                        <a href="{{ route('bulletin-boards.events.edit', $event) }}" class="btn btn-warning">Éditer</a>
                        <form action="{{ route('bulletin-boards.events.destroy', $event) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $events->links() }}
    </div>
@endsection
