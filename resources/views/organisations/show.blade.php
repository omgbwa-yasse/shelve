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

    <a href="{{ route('organisations.rooms.create', $organisation) }}" class="btn btn-secondary mt-6">Associer un local</a>
    <a href="{{ route('organisations.contacts.create', $organisation) }}" class="btn btn-success mt-2">Ajouter un contact</a>


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

        <a href="{{ route('organisations.activities.create', $organisation) }}" class="btn btn-primary mt-3">Associer à un activité</a>
        <ul class="list-group">
            @if($organisation->rooms == NULL)
                Cette unité n'a d'activité
            @else
                @foreach($organisation->activities as $activity )
                    <li class="list-group-item d-flex justify-content-between align-items-center" >
                        {{ $activity->code }} - {{ $activity->name }}
                            <form action="{{ route('organisations.activities.destroy', [$organisation, $activity]) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"> Supprimer </button>
                            </form>
                    </li>
                @endforeach
            @endif
        </ul>

        <h3 class="mt-4">Contacts associés</h3>
        @if($organisation->contacts && $organisation->contacts->count())
            <ul class="list-group">
                @foreach($organisation->contacts as $contact)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="text-break">
                            <span class="badge bg-secondary me-2">{{ $contact->type }}</span>
                            {{ $contact->value }}
                            @if($contact->label)
                                <small class="text-muted">— {{ $contact->label }}</small>
                            @endif
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('organisations.contacts.show', [$organisation, $contact]) }}" class="btn btn-sm btn-outline-secondary">Voir</a>
                            <a href="{{ route('organisations.contacts.edit', [$organisation, $contact]) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                            <form action="{{ route('organisations.contacts.destroy', [$organisation, $contact]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce contact ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-muted">Aucun contact associé.</div>
        @endif

    </div>
@endsection
