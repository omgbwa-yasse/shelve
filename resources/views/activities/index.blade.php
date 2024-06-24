@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Plan de classement </h1>
        <a href="{{ route('activities.create') }}" class="btn btn-primary mb-3">Ajouter une activité </a>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Observation</th>
                    <th>Parent</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($activities as $activity)
                    <tr>
                        <td>{{ $activity->code }}</td>
                        <td>{{ $activity->name }}</td>
                        <td>{{ $activity->observation }}</td>
                        <td>{{ $activity->parent->code ?? '' }}  {{ $activity->parent->name ?? 'Mission' }}</td>
                        <td>
                            <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-info">Paramètres</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
