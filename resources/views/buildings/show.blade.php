@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Bâtiment Details</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ $building->name }}</h5>
                <p class="card-text">{{ $building->description }}</p>
                <p class="card-text">
                    <strong>Visibilité:</strong>
                    <span class="badge bg-{{ $building->visibility == 'public' ? 'success' : ($building->visibility == 'private' ? 'danger' : 'warning') }}">
                        @switch($building->visibility)
                            @case('public')
                                Public
                                @break
                            @case('private')
                                Privé
                                @break
                            @case('inherit')
                                Hériter
                                @break
                            @default
                                N/A
                        @endswitch
                    </span>
                </p>
                <a href="{{ route('buildings.index') }}" class="btn btn-secondary btn-sm">Back</a>
                <a href="{{ route('buildings.edit', $building->id) }}" class="btn btn-warning btn-sm">Edit</a>
                <form action="{{ route('buildings.destroy', $building->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this building?')">Delete</button>
                </form>
                <a href="{{ route('floors.create', $building) }}" class="btn btn-warning btn-sm align-right">Ajouter un niveau au bâtiment</a>
            </div>
        </div>

        @if($building->floors->isEmpty())
            Ce bâtiment n'est pas en étage
        @else
            <div class="list-group">
                @foreach($building->floors as $floor)
                    <a href="{{ route('floors.show',  [$building , $floor] ) }}" class="list-group-item list-group-item-action">{{ $floor->name }} : {{ $floor->description }} </a>
                @endforeach
            </div>
        @endif

    </div>
@endsection
