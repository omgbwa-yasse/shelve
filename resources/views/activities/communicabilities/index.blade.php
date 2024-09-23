@extends('layouts.app')

@section('content')
    <h1>Détails sur le délai de transfert </h1>
    Activité :
        <a href="{{ route('activities.show', $activity->id)}}">
            {{ $activity->code }} : {{ $activity->name }}
        </a>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
                <tr>
                    <td>{{ $activity->communicability->code }}: {{ $activity->communicability->duration }} ans, {{ $activity->communicability->name }}</td>
                    <td>
                        <a href="{{ route('activities.communicabilities.edit', [$activity->id, $activity->communicability->id]) }}" class="btn btn-sm btn-primary">Edit</a>
                        <form action="{{ route('activities.communicabilities.destroy', [$activity->id, $activity->communicability->id]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this communicability?')">Delete</button>
                        </form>
                    </td>
                </tr>
        </tbody>
    </table>
@endsection
