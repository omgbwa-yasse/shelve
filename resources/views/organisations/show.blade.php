@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $organisation->name }}</h1>
        <p><strong>Code:</strong> {{ $organisation->code }}</p>
        <p><strong>Description:</strong> {{ $organisation->description }}</p>
        <p><strong>Parent Organisation:</strong> {{ $organisation->parent ? $organisation->parent->name : 'None' }}</p>
        <a href="{{ route('organisations.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('organisations.edit', $organisation->id) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('organisations.destroy', $organisation->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this organisation?')">Delete</button>
        </form>
        <a href="{{ route('organisations.rooms.create', $organisation) }}" class="btn btn-secondary">Associer un local</a>
        <ul class="list-group">
            @if($organisation->rooms == NULL)
                Cette unité n'a aucun local d'archives
            @else
                @foreach($organisation->rooms as $room )
                    <li class="list-group-item d-flex justify-content-between align-items-center" >
                        {{ $room->name }}
                        <span class="badge bg-secondary badge-pill">
                            <form action="{{ route('organisations.rooms.destroy', [$organisation, $room]) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                Supprimer
                            </form>
                        </span>
                    </li>
                @endforeach
            @endif
        </ul>

    </div>
@endsection
