@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Activity for {{ $organisationActivity->organisation->name }}</h1>
    <form action="{{ route('organisations.activities.update', [$organisationActivity->organisation, $organisationActivity]) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="activity_id" class="form-label">Activity</label>
            <select name="activity_id" id="activity_id" class="form-select">
                @foreach ($availableActivities as $activity)
                    <option value="{{ $activity->id }}" {{ $activity->id == $organisationActivity->activity_id ? 'selected' : '' }}>{{ $activity->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Activity</button>
    </form>
</div>
@endsection
