@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Activities for {{ $organisation ?? ''}}</h1>
    <a href="{{ route('organisations.activities.create', $organisation) }}" class="btn btn-primary">Add Activity</a>
    <table class="table">
        <thead>
            <tr>
                <th>Activity</th>
                <th>Creator</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($activities as $activity)
                <tr>
                    <td>{{ $activity->name }}</td>
                    <td>{{ $activity->creator->name }}</td>
                    <td>
                        <a href="{{ route('organisations.activities.show', [$organisation, $activity]) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('organisations.activities.edit', [$organisation, $activity]) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('organisations.activities.destroy', [$organisation, $activity]) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this activity?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
