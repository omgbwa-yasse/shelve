@extends('layouts.app')

@section('content')
    <h1>Retentions for Activity: {{ $activity->name }}</h1>

    <a href="{{ route('activities.retentions.create', $activity->id) }}" class="btn btn-primary mb-3">Add Retention</a>

    <table class="table">
        <thead>
            <tr>
                <th>Retention</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retentions as $retentionActivity)
                <tr>
                    <td>{{ $retentionActivity->retention->name }}</td>
                    <td>
                        <a href="{{ route('activities.retentions.edit', [$activity->id, $retentionActivity->id]) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('activities.retentions.destroy', [$activity->id, $retentionActivity->id]) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
