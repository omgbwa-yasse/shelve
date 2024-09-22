@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Retention to Activity: {{ $activity->name }}</h1>
    <form action="{{ route('activities.retentions.store', $activity->id) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="retention_id">Retention</label>
            <select class="form-control" id="retention_id" name="retention_id">
                @foreach($retentions as $retention)
                <option value="{{ $retention->id }}">Règle n° {{ $retention->code }} durée {{ $retention->duration }} ans </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Retention</button>
    </form>
</div>
@endsection
