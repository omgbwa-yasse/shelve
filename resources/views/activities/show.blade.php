@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Détails de l'activité</h1>

        <table class="table">
            <tr>
                <th>Code</th>
                <td>{{ $activity->code }}</td>
            </tr>
            <tr>
                <th>Name</th>
                <td>{{ $activity->name }}</td>
            </tr>
            <tr>
                <th>Observation</th>
                <td>{{ $activity->observation }}</td>
            </tr>
            @if ($activity->parent_id != NULL)
                <tr>
                    <th>Activité parent</th>
                    <td>{{ $activity->parent->code }} - {{ $activity->parent->name }}</td>
                </tr>
            @endif
        </table>

        @if ($activity->communicability != NULL)
            <h2>Durée de conservation dans les bureaux</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Durée</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $activity->communicability->code }}</td>
                        <td>{{ $activity->communicability->duration }} ans</td>
                        <td>
                            <!-- Add any actions here -->
                        </td>
                    </tr>
                </tbody>
            </table>
        @else
            <p>Aucun délai de conservation dans les bureaux</p>
        @endif

        <div class="-ml-3">
            <a href="{{ route('activities.communicabilities.create', $activity) }}" class="btn btn-secondary">Ajouter un délai avant le transfert</a>
        </div>

        <h2>Règles de conservation</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Règle de conservation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($activity->retentions as $retention)
                    <tr>
                        <td>{{ $retention->code }} - {{ $retention->duration }} ans, {{ $retention->description ?? 'sans description' }}</td>
                        <td>
                            <a href="{{ route('activities.retentions.edit', [$activity->id, $retention->id]) }}" class="btn btn-primary btn-sm">Modifier la règle</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Durée de conservation héritée</h2>
        @php
            $activity = $activity;
            $level = 1;
        @endphp
        @while ($activity->parent_id != 0)
            <h3>Hérité du parent  (n+{{ $level }}) : {{ $activity->parent->code }} - {{ $activity->parent->name }}</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Règle de conservation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($activity->parent->retentions as $retention)
                        <tr>
                            <td>{{ $retention->code }} - {{ $retention->duration }} ans, {{ $retention->description ?? 'sans description' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @php
                $activity = $activity->parent;
                $level++;
            @endphp
        @endwhile

        <div class="mt-3">
            <a href="{{ route('activities.index') }}" class="btn btn-secondary">Back</a>
            <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this activity?')">Delete</button>
            </form>
        </div>

        <hr>

        <div class="-ml-3">
            <a href="{{ route('activities.retentions.create', $activity) }}" class="btn btn-secondary">Ajouter un règle de conservation</a>
        </div>
    </div>
@endsection
