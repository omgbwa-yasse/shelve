@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ __('Activity Details') }}</h1>

        <table class="table">
            <tr>
                <th>{{ __('Code') }}</th>
                <td>{{ $activity->code }}</td>
            </tr>
            <tr>
                <th>{{ __('Name') }}</th>
                <td>{{ $activity->name }}</td>
            </tr>
            <tr>
                <th>{{ __('Observation') }}</th>
                <td>{{ $activity->observation }}</td>
            </tr>
            @if ($activity->parent_id != NULL)
                <tr>
                    <th>{{ __('Parent Activity') }}</th>
                    <td>{{ $activity->parent->code }} - {{ $activity->parent->name }}</td>
                </tr>
            @endif
        </table>

        @if ($activity->communicability != NULL)
            <h2>Délai de communicabilité</h2>
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
            <p>Aucun délai de communicabilité défini.</p>
        @endif

        <div class="-ml-3">
            <a href="{{ route('activities.communicabilities.create', $activity) }}" class="btn btn-secondary">Définir la communicabilité</a>
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

        <div class="alert alert-info">
            La règle effective doit être définie directement sur la classe utilisée par la notice. Cette règle unique alimente automatiquement l’échéance et le sort final.
        </div>

        <div class="mt-3">
            <a href="{{ route('activities.index') }}" class="btn btn-secondary">Retour</a>
            <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-warning">Modifier</a>
            <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer cette activité ?')">Supprimer</button>
            </form>
        </div>

        <hr>

        <div class="-ml-3">
            <a href="{{ route('activities.retentions.create', $activity) }}" class="btn btn-secondary">Ajouter un règle de conservation</a>
        </div>
    </div>
@endsection
