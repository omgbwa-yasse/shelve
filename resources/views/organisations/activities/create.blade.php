@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Activity to {{ $organisation->name }}</h1>
    <form action="{{ route('organisations.activities.store', $organisation) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="activity_id" class="form-label">Activity</label>
            <select name="activity_id" id="activity_id" class="form-select">
                @foreach ($availableActivities as $activity)
                    <option value="{{ $activity->id }}">{{ $activity->code }} - {{ $activity->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Activity</button>
    </form>
</div>
@endsection
