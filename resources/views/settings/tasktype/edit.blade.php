@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Task Type</h1>
        <form action="{{ route('tasktype.update', $taskType->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" class="form-control" value="{{ $taskType->name }}" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control">{{ $taskType->description }}</textarea>
            </div>
            <div class="form-group">
                <label for="activity_id">Activity</label>
                <select name="activity_id" class="form-control" required>
                    @foreach($activities as $activity)
                        <option value="{{ $activity->id }}" {{ $taskType->activity_id == $activity->id ? 'selected' : '' }}>{{ $activity->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
