@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Retention Details for Activity: {{ $activity->name }}</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Retention Name: {{ $retentionActivity->retention->name }}</h5>
            <p class="card-text">Retention Description: {{ $retentionActivity->retention->description }}</p>
            <a href="{{ route('activities.retentions.edit', [$activity->id, $retentionActivity->id]) }}" class="btn btn-warning">Edit</a>
            <form action="{{ route('activities.retentions.destroy', [$activity->id, $retentionActivity->id]) }}" method="POST" style="display: inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
            <a href="{{ route('activities.retentions.index', $activity->id) }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
