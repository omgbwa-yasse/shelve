@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Task Type</h1>
        <form action="{{ route('tasktype.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label for="color">Color</label>
                <input type="color" name="color" class="form-control">
                <small class="form-text text-muted">Choose a color for this task type</small>
            </div>
            <div class="form-group">
                <label for="activity_id">Activity</label>
                <select name="activity_id" class="form-control" required>
                    @foreach($activities as $activity)
                        <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
