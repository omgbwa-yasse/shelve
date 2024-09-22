@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Activity Details</h1>
        <table class="table">
            <tr>
                <th>ID</th>
                <td>{{ $activity->id }}</td>
            </tr>
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
            <tr>
                <th>Parent ID</th>
                <td>{{ $activity->parent_id }}</td>
            </tr>
        </table>
        <a href="{{ route('activities.index') }}" class="btn btn-secondary">Back</a>
        <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" style="display: inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this activity?')">Delete</button>
        </form>
    <hr>
    <div class="-ml-3">
            <a href="{{ route('activities.retentions.create',$activity ) }}" class="btn btn-secondary">Ajouter un règle de conservation</a>
    </div>
    </div>
@endsection
