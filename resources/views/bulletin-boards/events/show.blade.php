<!-- resources/views/bulletin-boards/events/show.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $event->name }}</h1>
        <p>{{ $event->description }}</p>
        <p><strong>Date de Début:</strong> {{ $event->start_date }}</p>
        <p><strong>Date de Fin:</strong> {{ $event->end_date }}</p>
        <p><strong>Lieu:</strong> {{ $event->location }}</p>
        <p><strong>Créé par:</strong> {{ $event->user->name }}</p>
        <h2>Organisations</h2>
        <ul>
            @foreach($event->bulletinBoard->organisations as $organisation)
                <li>{{ $organisation->name }}</li>
            @endforeach
        </ul>
        <a href="{{ route('bulletin-boards.events.edit', $event) }}" class="btn btn-warning">Éditer</a>
        <form action="{{ route('bulletin-boards.events.destroy', $event) }}" method="POST" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Supprimer</button>
        </form>
    </div>
@endsection
